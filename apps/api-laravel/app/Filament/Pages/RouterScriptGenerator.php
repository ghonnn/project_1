<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminOptions;
use App\Models\AuditLog;
use App\Models\RadiusServer;
use App\Models\Router;
use App\Models\RouterScriptTemplate;
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

    public static function canAccess(): bool
    {
        return Auth::user()?->hasPermission('router.manage') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

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
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set): void {
                                $set('router_id', null);
                                $set('radius_server_id', null);
                            }),
                        Forms\Components\Select::make('router_id')
                            ->options(fn (Forms\Get $get) => AdminOptions::routers($get('tenant_id')))
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('radius_server_id')
                            ->options(fn (Forms\Get $get) => AdminOptions::radiusServers($get('tenant_id')))
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

        $template = RouterScriptTemplate::query()
            ->where('vendor', 'mikrotik')
            ->where('os_version', $data['os_version'])
            ->where('script_type', $data['script_type'])
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id'))
            ->orderByRaw('tenant_id is null')
            ->first();

        $variables = $this->variables($router, $server, $data);
        $this->script = $template
            ? $template->render($variables)
            : str_replace(array_map(fn ($key) => '{{'.$key.'}}', array_keys($variables)), array_values($variables), RouterScriptTemplate::defaultTemplate($data['script_type']));

        AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => Auth::id(),
            'action' => 'router_script.generated',
            'entity_type' => 'routers',
            'entity_id' => $router->id,
            'new_values' => [
                'template_id' => $template?->id,
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
     * @return array<string, string>
     */
    private function variables(Router $router, RadiusServer $server, array $data): array
    {
        return [
            'script_type' => (string) $data['script_type'],
            'os_version' => (string) $data['os_version'],
            'radius_service' => strtolower((string) $data['script_type']) === 'hotspot' ? 'hotspot' : 'ppp',
            'radius_server_ip' => (string) $server->host,
            'radius_secret' => (string) $server->shared_secret,
            'auth_port' => (string) $server->auth_port,
            'acct_port' => (string) $server->acct_port,
            'router_name' => (string) $router->router_name,
            'router_hostname' => (string) $router->hostname,
            'interim_update' => '5m',
            'service_profile' => (string) ($data['service_profile'] ?? ''),
        ];
    }
}
