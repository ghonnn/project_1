<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RouterResource\Pages;
use App\Filament\Resources\RouterResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\RadiusServer;
use App\Models\Router;
use App\Services\RouterProvisioningService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class RouterResource extends Resource
{
    protected static ?string $model = Router::class;

    protected static ?string $navigationGroup = 'Jaringan';

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static ?string $navigationLabel = 'Router';

    protected static ?string $modelLabel = 'Router MikroTik';

    protected static ?string $pluralModelLabel = 'Router dan Server';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Placeholder::make('router_help')
                    ->label('')
                    ->content('IP Public: router terpasang langsung di MikroTik. VPN Radius: router dihubungkan via jalur VPN Radius yang disediakan.')
                    ->columnSpanFull(),
                Forms\Components\Select::make('tenant_id')->label('Tenant')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\TextInput::make('router_name')
                    ->label('Nama Router / Hostname')
                    ->required()
                    ->maxLength(80),
                Forms\Components\Hidden::make('hostname')
                    ->dehydrated(true),
                Forms\Components\Hidden::make('router_role')
                    ->default('pppoe_router')
                    ->dehydrated(true),
                Forms\Components\Select::make('connection_type')
                    ->label('Tipe Koneksi')
                    ->options(['ip_public' => 'IP Public', 'vpn_radius' => 'VPN Radius'])
                    ->default('ip_public')
                    ->required(),
                Forms\Components\TextInput::make('management_ip')->label('IP Address')->required()->maxLength(45),
                Forms\Components\TextInput::make('radius_secret')
                    ->label('Secret Radius')
                    ->password()
                    ->revealable()
                    ->readOnly()
                    ->default(fn (): string => self::generateRadiusSecret())
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, mixed $state): void {
                        if (blank($state)) {
                            $component->state(self::generateRadiusSecret());
                        }
                    })
                    ->dehydrated(true)
                    ->maxLength(12),
                Forms\Components\TextInput::make('vendor')->label('Vendor')->maxLength(50),
                Forms\Components\TextInput::make('model')->label('Model')->maxLength(80),
                Forms\Components\TextInput::make('serial_number')->label('Serial Number')->maxLength(80),
                Forms\Components\TextInput::make('site_name')->label('Nama Site')->maxLength(80),
                Forms\Components\TextInput::make('public_ip')->label('IP Public')->maxLength(45),
                Forms\Components\TextInput::make('latitude')->label('Latitude')->numeric()->maxLength(16),
                Forms\Components\TextInput::make('longitude')->label('Longitude')->numeric()->maxLength(16),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(['draft' => 'Draft', 'active' => 'Aktif', 'maintenance' => 'Maintenance', 'inactive' => 'Non Aktif'])
                    ->default('draft')
                    ->required(),
                Forms\Components\Section::make('Script MikroTik')
                    ->columns(2)
                    ->schema([
                        self::scriptProfileInput('snmp_community', 'SNMP Community', 50),
                        self::scriptProfileInput('snmp_allowed_address', 'SNMP Allowed Address', 45),
                        self::scriptProfileInput('time_zone', 'Time Zone', 50),
                        self::scriptProfileInput('dns_servers', 'DNS Server', 120),
                        self::scriptProfileInput('ntp_servers', 'NTP Server', 120),
                        self::scriptProfileInput('radius_src_address', 'Radius Src Address', 45),
                        self::scriptProfileInput('radius_incoming_port', 'Radius Incoming Port', 5, true),
                        self::scriptProfileInput('pool_name', 'Nama IP Pool', 50),
                        self::scriptProfileInput('pool_comment', 'Comment IP Pool', 120),
                        self::scriptProfileInput('pool_ranges', 'Range IP Pool', 120),
                        self::scriptProfileInput('isolir_pool_name', 'Nama IP Pool Isolir', 50),
                        self::scriptProfileInput('isolir_pool_comment', 'Comment IP Pool Isolir', 120),
                        self::scriptProfileInput('isolir_pool_ranges', 'Range IP Pool Isolir', 120),
                        self::scriptProfileInput('ppp_profile_name', 'Nama PPP Profile', 50),
                        self::scriptProfileInput('pppoe_service_name', 'PPPoE Service Name', 50),
                        self::scriptProfileInput('pppoe_server_interface', 'PPPoE Server Interface', 80, false, 'bridge-LAN / ether2'),
                        self::scriptProfileInput('ppp_local_address', 'PPP Local Address', 45),
                        self::scriptProfileInput('isolir_profile_name', 'Nama PPP Profile Isolir', 50),
                        self::scriptProfileInput('isolir_local_address', 'PPP Local Address Isolir', 45),
                        self::scriptProfileInput('isolir_redirect_host', 'Host Redirect Isolir', 120),
                        self::scriptProfileInput('isolir_redirect_port', 'Port Redirect Isolir', 5, true),
                        self::scriptProfileInput('hotspot_server_name', 'Hotspot Server Name', 50),
                        self::scriptProfileInput('hotspot_interface', 'Hotspot / WiFi Interface', 80, false, 'bridge-WIFI / wlan1'),
                        self::scriptProfileInput('hotspot_profile_name', 'Hotspot Profile Name', 50),
                    ]),
            ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalizeRouterSettings(array $data, ?Router $router = null): array
    {
        $data['hostname'] = trim((string) ($data['router_name'] ?? $router?->router_name ?? $router?->hostname ?? ''));
        $data['router_role'] = $data['router_role'] ?? $router?->router_role ?? 'pppoe_router';

        if (blank($data['radius_secret'] ?? null)) {
            $data['radius_secret'] = self::generateRadiusSecret();
        }

        $profile = $data['snmp_profile'] ?? [];

        $data['snmp_profile'] = RouterProvisioningService::mergeDefaultSnmpProfile(
            is_array($profile) ? $profile : [],
            $router,
            $data
        );

        return $data;
    }

    private static function generateRadiusSecret(): string
    {
        $secret = '';

        for ($i = 0; $i < 12; $i++) {
            $secret .= (string) random_int(0, 9);
        }

        return $secret;
    }

    private static function snmpStatusLabel(?string $state): string
    {
        return match ($state) {
            'reachable' => 'Aktif / Reachable',
            'unreachable' => 'Tidak Terhubung',
            default => 'Belum Dikonfigurasi',
        };
    }

    private static function scriptProfileInput(
        string $key,
        string $label,
        int $maxLength,
        bool $numeric = false,
        ?string $placeholder = null
    ): Forms\Components\TextInput {
        $input = Forms\Components\TextInput::make('snmp_profile.'.$key)
            ->label($label)
            ->default(fn (?Router $record): string => RouterProvisioningService::defaultSnmpProfile($record)[$key] ?? '')
            ->afterStateHydrated(function (Forms\Components\TextInput $component, mixed $state, ?Router $record) use ($key): void {
                if (blank($state)) {
                    $component->state(RouterProvisioningService::defaultSnmpProfile($record)[$key] ?? '');
                }
            })
            ->maxLength($maxLength);

        if ($numeric) {
            $input->numeric();
        }

        if ($placeholder !== null) {
            $input->placeholder($placeholder);
        }

        return $input;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Router dan Server')
            ->columns([
                Tables\Columns\TextColumn::make('router_name')->label('Nama Router')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('connection_type')
                    ->label('Tipe Koneksi')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'vpn_radius' => 'VPN Radius',
                        default => 'IP Public',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'vpn_radius' ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('management_ip')->label('IP Address')->searchable(),
                Tables\Columns\TextColumn::make('radius_secret')
                    ->label('Secret')
                    ->formatStateUsing(fn (?string $state): string => $state ? str_repeat('*', min(strlen($state), 16)) : '-'),
                Tables\Columns\TextColumn::make('primaryNasDevice.radiusServer.name')
                    ->label('FreeRadius')
                    ->placeholder('Belum terhubung')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('primaryNasDevice.nas_ip_address')
                    ->label('NAS IP')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pppoe_online')
                    ->label('Langganan Online')
                    ->state(fn (Router $record): int => $record->pppoeOnlineCount())
                    ->alignCenter()
                    ->color('success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('hotspot_online')
                    ->label('Voucher Online')
                    ->state(fn (Router $record): int => $record->hotspotOnlineCount())
                    ->alignCenter()
                    ->color('info')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('script_download')->label('Script')->state('Download')->badge()->color('info'),
                Tables\Columns\TextColumn::make('snmp_status')
                    ->label('SNMP Monitoring')
                    ->formatStateUsing(fn (?string $state): string => self::snmpStatusLabel($state))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'reachable' => 'success',
                        'unreachable' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'maintenance' => 'warning',
                    'inactive' => 'danger',
                    default => 'gray',
                }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('connect_radius')
                    ->label('Hubungkan Radius')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->modalHeading(fn (Router $record): string => 'Hubungkan Radius - '.$record->router_name)
                    ->form([
                        Forms\Components\Select::make('radius_server_id')
                            ->label('FreeRadius Server')
                            ->options(fn (Router $record): array => AdminOptions::radiusServers($record->tenant_id))
                            ->default(fn (Router $record): ?string => $record->primaryNasDevice?->radius_server_id)
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('secret')
                            ->label('NAS Secret')
                            ->password()
                            ->revealable()
                            ->default(fn (Router $record): ?string => $record->radius_secret ?: $record->primaryNasDevice?->secret)
                            ->helperText('Harus sama dengan secret di menu /radius MikroTik.')
                            ->required()
                            ->maxLength(80),
                    ])
                    ->action(function (Router $record, array $data): void {
                        $server = RadiusServer::query()
                            ->where('tenant_id', $record->tenant_id)
                            ->where('status', 'active')
                            ->findOrFail($data['radius_server_id']);

                        app(RouterProvisioningService::class)->ensureNasDevice($record, $server, $data['secret']);

                        Notification::make()
                            ->title('Router terhubung ke FreeRadius')
                            ->body('NAS client dibuat/diupdate dan siap dipakai oleh PPPoE/Hotspot.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('snmp_monitoring')
                    ->label('SNMP')
                    ->icon('heroicon-o-signal')
                    ->color(fn (Router $record): string => $record->snmp_status === 'reachable' ? 'success' : 'gray')
                    ->modalHeading(fn (Router $record): string => 'SNMP Monitoring - '.$record->router_name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (Router $record): HtmlString => self::snmpMonitoringContent($record)),
                Tables\Actions\Action::make('test_snmp')
                    ->label('Test SNMP')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (Router $record): void {
                        $result = app(RouterProvisioningService::class)->testSnmp($record);

                        Notification::make()
                            ->title(match ($result['status']) {
                                'reachable' => 'SNMP reachable',
                                'unreachable' => 'SNMP belum reachable',
                                default => 'SNMP belum lengkap',
                            })
                            ->body($result['message'])
                            ->color($result['status'] === 'reachable' ? 'success' : 'warning')
                            ->send();
                    }),
                Tables\Actions\Action::make('download_script')
                    ->label('Download Script')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (Router $record) {
                        $script = app(RouterProvisioningService::class)->buildRouterScript($record);

                        return response()->streamDownload(fn () => print($script), $record->hostname.'-script.rsc');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InterfacesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRouters::route('/'),
            'create' => Pages\CreateRouter::route('/create'),
            'edit' => Pages\EditRouter::route('/{record}/edit'),
        ];
    }

    private static function snmpMonitoringContent(Router $record): HtmlString
    {
        $record->loadMissing(['interfaces', 'radiusUsers.service.customer', 'primaryNasDevice.radiusServer']);

        $interfaces = $record->interfaces
            ->map(fn ($interface): string => '<tr><td>'.$interface->interface_name.'</td><td>'.($interface->interface_type ?: '-').'</td><td>'.($interface->ip_address ?: '-').'</td><td>'.strtoupper($interface->status).'</td></tr>')
            ->implode('');

        $pppoeUsers = $record->radiusUsers
            ->filter(fn ($user): bool => $user->status === 'active' && in_array(strtoupper((string) $user->service?->connection_type), ['PPP', 'PPPOE'], true))
            ->map(fn ($user): string => '<tr><td>'.$user->username.'</td><td>'.($user->service?->cid ?: '-').'</td><td>'.($user->service?->billing_profile_name ?: '-').'</td><td>'.($user->service?->customer?->name ?: '-').'</td></tr>')
            ->implode('');

        $hotspotUsers = $record->radiusUsers
            ->filter(fn ($user): bool => $user->status === 'active' && in_array(strtoupper((string) $user->service?->connection_type), ['HOTSPOT', 'WIFI'], true))
            ->map(fn ($user): string => '<tr><td>'.$user->username.'</td><td>'.($user->service?->cid ?: '-').'</td><td>'.($user->service?->billing_profile_name ?: '-').'</td><td>'.($user->service?->customer?->name ?: '-').'</td></tr>')
            ->implode('');

        return new HtmlString('
            <div style="display:grid;gap:18px">
                <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px">
                    <div><strong>Status SNMP:</strong> '.e(self::snmpStatusLabel($record->snmp_status)).'</div>
                    <div><strong>FreeRadius:</strong> '.e($record->primaryNasDevice?->radiusServer?->name ?? 'Belum terhubung').'</div>
                    <div><strong>NAS IP:</strong> '.e($record->primaryNasDevice?->nas_ip_address ?? '-').'</div>
                    <div><strong>Langganan Online:</strong> '.e((string) $record->pppoeOnlineCount()).'</div>
                    <div><strong>Voucher Online:</strong> '.e((string) $record->hotspotOnlineCount()).'</div>
                </div>
                '.self::snmpTable('Interface Router', ['Interface', 'Tipe', 'IP Address', 'Status'], $interfaces).'
                '.self::snmpTable('PPPoE Active', ['Username', 'CID', 'Profile', 'Pelanggan'], $pppoeUsers).'
                '.self::snmpTable('Hotspot/WiFi Active', ['Username', 'CID', 'Profile', 'Pelanggan'], $hotspotUsers).'
            </div>
        ');
    }

    /**
     * @param array<int, string> $headers
     */
    private static function snmpTable(string $title, array $headers, string $rows): string
    {
        $head = collect($headers)->map(fn (string $header): string => '<th style="text-align:left;padding:8px;border-bottom:1px solid #334155">'.$header.'</th>')->implode('');
        $body = $rows !== '' ? $rows : '<tr><td colspan="'.count($headers).'" style="padding:8px;color:#94a3b8">Belum ada data</td></tr>';

        return '<section><h3 style="font-weight:700;margin-bottom:8px">'.$title.'</h3><table style="width:100%;border-collapse:collapse"><thead><tr>'.$head.'</tr></thead><tbody>'.$body.'</tbody></table></section>';
    }
}
