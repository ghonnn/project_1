<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminOptions;
use App\Models\AuditLog;
use App\Models\RadiusServer;
use App\Models\Router;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RouterScriptGenerator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Jaringan';

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationLabel = 'Generator Script';

    protected static ?int $navigationSort = 50;

    protected static string $view = 'filament.pages.router-script-generator';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public ?string $script = null;

    public function mount(): void
    {
        $this->form->fill([
            'os_version' => 'ROS7',
            'script_type' => 'PPPoE',
            'service_profile' => '100M',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Router and RADIUS')
                    ->columns(1)
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->options(fn () => AdminOptions::tenants())
                            ->searchable()
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('router_id')
                            ->options(fn () => AdminOptions::routers())
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('radius_server_id')
                            ->options(fn () => AdminOptions::radiusServers())
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('os_version')
                            ->options(['ROS6' => 'RouterOS v6', 'ROS7' => 'RouterOS v7'])
                            ->required(),
                        Forms\Components\Select::make('script_type')
                            ->options(['PPPoE' => 'PPPoE', 'Hotspot' => 'Hotspot'])
                            ->required(),
                        Forms\Components\TextInput::make('service_profile')
                            ->label('Service profile')
                            ->placeholder('100M'),
                    ]),
            ])
            ->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();
        $tenantId = $data['tenant_id'];

        $router = Router::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($data['router_id']);

        $server = RadiusServer::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($data['radius_server_id']);

        $this->script = $this->buildScript($router, $server, $data);

        AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => Auth::id(),
            'action' => 'router_script.generated',
            'entity_type' => 'routers',
            'entity_id' => $router->id,
            'new_values' => [
                'radius_server_id' => $server->id,
                'os_version' => $data['os_version'],
                'script_type' => $data['script_type'],
                'service_profile' => $data['service_profile'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Notification::make()
            ->title('Router script generated')
            ->success()
            ->send();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildScript(Router $router, RadiusServer $server, array $data): string
    {
        $service = strtolower((string) $data['script_type']) === 'hotspot' ? 'hotspot' : 'ppp';
        $profile = $data['service_profile'] ?? null;

        $lines = [
            '# NEXBIL MikroTik '.$data['script_type'].' '.$data['os_version'].' RADIUS script',
            '# Router: '.$router->router_name.' / '.$router->hostname,
            '/radius add service='.$service.' address='.$server->host.' secret="'.$server->shared_secret.'" authentication-port='.$server->auth_port.' accounting-port='.$server->acct_port.' timeout=300ms',
        ];

        if ($service === 'ppp') {
            $lines[] = '/ppp aaa set use-radius=yes accounting=yes interim-update=5m';
            if ($profile) {
                $lines[] = '/ppp profile add name="'.$profile.'" use-encryption=yes only-one=yes';
            }
        } else {
            $lines[] = '/ip hotspot profile set [ find default=yes ] use-radius=yes';
            if ($profile) {
                $lines[] = '/ip hotspot user profile add name="'.$profile.'" shared-users=1';
            }
        }

        $lines[] = '/system identity set name="'.$router->hostname.'"';

        return implode("\n", $lines);
    }
}
