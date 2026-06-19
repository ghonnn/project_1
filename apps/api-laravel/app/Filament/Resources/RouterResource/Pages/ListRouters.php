<?php

namespace App\Filament\Resources\RouterResource\Pages;

use App\Filament\Resources\RouterResource;
use App\Filament\Support\AdminOptions;
use App\Models\RadiusServer;
use Filament\Forms;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

class ListRouters extends ListRecords
{
    protected static string $resource = RouterResource::class;

    protected static ?string $title = 'Router dan Server';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('set_active')
                    ->label('Set Aktif')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn () => Notification::make()->title('Pilih router pada tabel lalu edit status menjadi Active.')->info()->send()),
                Actions\Action::make('set_inactive')
                    ->label('Non Aktif')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn () => Notification::make()->title('Pilih router pada tabel lalu edit status menjadi Inactive.')->warning()->send()),
                Actions\Action::make('delete_hint')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn () => Notification::make()->title('Gunakan tombol edit/delete pada baris router yang ingin dihapus.')->danger()->send()),
            ])
                ->label('Menu')
                ->icon('heroicon-o-bars-3')
                ->color('info')
                ->button(),
            Actions\CreateAction::make()
                ->label('Tambah')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modalHeading('Router MikroTik'),
            Actions\Action::make('server')
                ->label('Server')
                ->icon('heroicon-o-server-stack')
                ->color('danger')
                ->modalHeading('Data Server')
                ->modalDescription('Server name harus sama dengan service name di MikroTik, contoh PPPoE Servers -> Service Name atau Hotspot Servers -> Name.')
                ->form([
                    Forms\Components\Select::make('tenant_id')->label('Tenant')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                    Forms\Components\TextInput::make('name')->label('Server Name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('host')->label('IP Server Radius')->required()->maxLength(255),
                    Forms\Components\TextInput::make('shared_secret')->label('Secret')->password()->revealable()->required()->maxLength(255),
                    Forms\Components\TextInput::make('auth_port')->label('Auth Port')->numeric()->default(1812)->required(),
                    Forms\Components\TextInput::make('acct_port')->label('Acct Port')->numeric()->default(1813)->required(),
                ])
                ->action(function (array $data): void {
                    RadiusServer::create([
                        'tenant_id' => $data['tenant_id'],
                        'name' => $data['name'],
                        'host' => $data['host'],
                        'shared_secret' => $data['shared_secret'],
                        'auth_port' => $data['auth_port'],
                        'acct_port' => $data['acct_port'],
                        'status' => 'active',
                    ]);

                    Notification::make()->title('Server Radius berhasil ditambahkan')->success()->send();
                }),
            Actions\Action::make('static_routing')
                ->label('S.Routing')
                ->icon('heroicon-o-share')
                ->color('info')
                ->modalHeading('Static Routing VPN')
                ->modalDescription('Static routing digunakan jika router memakai jalur VPN Radius agar traffic Radius tetap melewati gateway yang stabil.')
                ->form([
                    Forms\Components\TextInput::make('gateway')
                        ->label('IP Address Gateway atau Interface Name')
                        ->placeholder('192.168.1.1')
                        ->required(),
                    Forms\Components\TextInput::make('radius_host')
                        ->label('IP Radius Server')
                        ->placeholder('10.20.1.19')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $script = '/ip route add dst-address='.$data['radius_host'].'/32 gateway='.$data['gateway'].' comment="NEXBIL Radius VPN route"';

                    Notification::make()
                        ->title('Script Static Routing')
                        ->body(new HtmlString('<code>'.$script.'</code>'))
                        ->success()
                        ->persistent()
                        ->send();
                }),
        ];
    }
}
