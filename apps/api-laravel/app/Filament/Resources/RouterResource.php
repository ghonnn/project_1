<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RouterResource\Pages;
use App\Filament\Resources\RouterResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\RadiusServer;
use App\Models\Router;
use Filament\Forms;
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
            ->columns(1)
            ->schema([
                Forms\Components\Placeholder::make('router_help')
                    ->label('')
                    ->content('IP Public: router terpasang langsung di MikroTik. VPN Radius: router dihubungkan via jalur VPN Radius yang disediakan.'),
                Forms\Components\Select::make('tenant_id')->label('Tenant')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\TextInput::make('router_name')->label('Nama Router')->required()->maxLength(255),
                Forms\Components\Select::make('connection_type')
                    ->label('Tipe Koneksi')
                    ->options(['ip_public' => 'IP Public', 'vpn_radius' => 'VPN Radius'])
                    ->default('ip_public')
                    ->required(),
                Forms\Components\TextInput::make('management_ip')->label('IP Address')->required()->maxLength(255),
                Forms\Components\TextInput::make('radius_secret')->label('Secret')->password()->revealable()->maxLength(255),
                Forms\Components\TextInput::make('hostname')->label('Hostname')->required()->maxLength(255),
                Forms\Components\TextInput::make('vendor')->label('Vendor')->maxLength(255),
                Forms\Components\TextInput::make('model')->label('Model')->maxLength(255),
                Forms\Components\TextInput::make('serial_number')->label('Serial Number')->maxLength(255),
                Forms\Components\Select::make('router_role')
                    ->options([
                        'core_router' => 'Core Router',
                        'aggregation_router' => 'Aggregation Router',
                        'edge_router' => 'Edge Router',
                        'pppoe_router' => 'PPPoE Router',
                        'bng' => 'BNG',
                        'wireless_gateway' => 'Wireless Gateway',
                        'pop_router' => 'POP Router',
                        'bts_router' => 'BTS Router',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('site_name')->label('Nama Site')->maxLength(255),
                Forms\Components\TextInput::make('public_ip')->label('IP Public')->maxLength(255),
                Forms\Components\TextInput::make('online_sessions')->label('Online')->numeric()->default(0),
                Forms\Components\TextInput::make('latitude')->label('Latitude')->numeric(),
                Forms\Components\TextInput::make('longitude')->label('Longitude')->numeric(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(['draft' => 'Draft', 'active' => 'Aktif', 'maintenance' => 'Maintenance', 'inactive' => 'Non Aktif'])
                    ->default('draft')
                    ->required(),
                Forms\Components\Select::make('snmp_status')
                    ->label('Status SNMP')
                    ->options(['not_configured' => 'Belum Dikonfigurasi', 'reachable' => 'Aktif', 'unreachable' => 'Tidak Terhubung'])
                    ->default('not_configured')
                    ->required(),
                Forms\Components\Section::make('Script MikroTik')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('snmp_profile.snmp_community')
                            ->label('SNMP Community')
                            ->default('NEXRADIUS'),
                        Forms\Components\TextInput::make('snmp_profile.snmp_allowed_address')
                            ->label('SNMP Allowed Address')
                            ->placeholder('103.142.202.19'),
                        Forms\Components\TextInput::make('snmp_profile.time_zone')
                            ->label('Time Zone')
                            ->default('Asia/Jakarta'),
                        Forms\Components\TextInput::make('snmp_profile.dns_servers')
                            ->label('DNS Server')
                            ->default('8.8.8.8,1.1.1.1'),
                        Forms\Components\TextInput::make('snmp_profile.ntp_servers')
                            ->label('NTP Server')
                            ->default('162.159.200.1,162.159.200.123'),
                        Forms\Components\TextInput::make('snmp_profile.radius_src_address')
                            ->label('Radius Src Address')
                            ->placeholder('103.142.203.19'),
                        Forms\Components\TextInput::make('snmp_profile.radius_incoming_port')
                            ->label('Radius Incoming Port')
                            ->numeric()
                            ->default(3799),
                        Forms\Components\TextInput::make('snmp_profile.pool_name')
                            ->label('Nama IP Pool')
                            ->default('NEXPOOL'),
                        Forms\Components\TextInput::make('snmp_profile.pool_comment')
                            ->label('Comment IP Pool')
                            ->default('Network : 10.200.192.0/20'),
                        Forms\Components\TextInput::make('snmp_profile.pool_ranges')
                            ->label('Range IP Pool')
                            ->placeholder('10.200.192.100-10.200.207.254'),
                        Forms\Components\TextInput::make('snmp_profile.isolir_pool_name')
                            ->label('Nama IP Pool Isolir')
                            ->default('NEXISOLIR'),
                        Forms\Components\TextInput::make('snmp_profile.isolir_pool_comment')
                            ->label('Comment IP Pool Isolir')
                            ->default('Network 10.200.208.0/23'),
                        Forms\Components\TextInput::make('snmp_profile.isolir_pool_ranges')
                            ->label('Range IP Pool Isolir')
                            ->placeholder('10.200.208.10-10.200.209.254'),
                        Forms\Components\TextInput::make('snmp_profile.ppp_profile_name')
                            ->label('Nama PPP Profile')
                            ->default('NEXRADIUS'),
                        Forms\Components\TextInput::make('snmp_profile.ppp_local_address')
                            ->label('PPP Local Address')
                            ->placeholder('10.200.192.1'),
                        Forms\Components\TextInput::make('snmp_profile.isolir_profile_name')
                            ->label('Nama PPP Profile Isolir')
                            ->default('NEXISOLIR'),
                        Forms\Components\TextInput::make('snmp_profile.isolir_local_address')
                            ->label('PPP Local Address Isolir')
                            ->placeholder('10.200.208.1'),
                        Forms\Components\TextInput::make('snmp_profile.isolir_redirect_host')
                            ->label('Host Redirect Isolir')
                            ->placeholder('103.253.27.164'),
                        Forms\Components\TextInput::make('snmp_profile.isolir_redirect_port')
                            ->label('Port Redirect Isolir')
                            ->numeric()
                            ->default(3125),
                    ]),
            ]);
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
                Tables\Columns\TextColumn::make('online_sessions')->label('Online')->alignCenter()->color('success')->weight('bold'),
                Tables\Columns\TextColumn::make('script_download')->label('Script')->state('Download')->badge()->color('info'),
                Tables\Columns\TextColumn::make('snmp_status')
                    ->label('SNMP Monitoring')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'reachable' => 'Aktif',
                        'unreachable' => 'Tidak Terhubung',
                        default => 'Belum Aktif',
                    })
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
                Tables\Actions\Action::make('snmp_monitoring')
                    ->label('SNMP')
                    ->icon('heroicon-o-signal')
                    ->color(fn (Router $record): string => $record->snmp_status === 'reachable' ? 'success' : 'gray')
                    ->modalHeading(fn (Router $record): string => 'SNMP Monitoring - '.$record->router_name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (Router $record): HtmlString => self::snmpMonitoringContent($record)),
                Tables\Actions\Action::make('set_snmp_active')
                    ->label('Set SNMP Aktif')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Router $record): bool => $record->snmp_status !== 'reachable')
                    ->action(fn (Router $record) => $record->update(['snmp_status' => 'reachable'])),
                Tables\Actions\Action::make('download_script')
                    ->label('Download Script')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (Router $record) {
                        $server = RadiusServer::query()
                            ->where('tenant_id', $record->tenant_id)
                            ->where('status', 'active')
                            ->first();

                        $script = self::buildRouterScript($record, $server);

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
        $record->loadMissing(['interfaces', 'radiusUsers.service.customer']);

        $interfaces = $record->interfaces
            ->map(fn ($interface): string => '<tr><td>'.$interface->interface_name.'</td><td>'.($interface->interface_type ?: '-').'</td><td>'.($interface->ip_address ?: '-').'</td><td>'.strtoupper($interface->status).'</td></tr>')
            ->implode('');

        $pppoeUsers = $record->radiusUsers
            ->filter(fn ($user): bool => $user->status === 'active' && strtolower((string) $user->service?->connection_type) !== 'hotspot')
            ->map(fn ($user): string => '<tr><td>'.$user->username.'</td><td>'.($user->service?->cid ?: '-').'</td><td>'.($user->service?->customer?->name ?: '-').'</td></tr>')
            ->implode('');

        $hotspotUsers = $record->radiusUsers
            ->filter(fn ($user): bool => $user->status === 'active' && strtolower((string) $user->service?->connection_type) === 'hotspot')
            ->map(fn ($user): string => '<tr><td>'.$user->username.'</td><td>'.($user->service?->cid ?: '-').'</td><td>'.($user->service?->customer?->name ?: '-').'</td></tr>')
            ->implode('');

        return new HtmlString('
            <div style="display:grid;gap:18px">
                <div><strong>Status SNMP:</strong> '.($record->snmp_status === 'reachable' ? 'Aktif' : 'Belum Aktif').'</div>
                '.self::snmpTable('Interface Router', ['Interface', 'Tipe', 'IP Address', 'Status'], $interfaces).'
                '.self::snmpTable('PPPoE Active', ['Username', 'CID', 'Pelanggan'], $pppoeUsers).'
                '.self::snmpTable('Hotspot Active', ['Username', 'CID', 'Pelanggan'], $hotspotUsers).'
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

    private static function buildRouterScript(Router $router, ?RadiusServer $server): string
    {
        $config = $router->snmp_profile ?? [];

        $snmpCommunity = self::config($config, 'snmp_community', 'NEXRADIUS');
        $snmpAllowedAddress = self::config($config, 'snmp_allowed_address', $router->public_ip ?: $router->management_ip);
        $timeZone = self::config($config, 'time_zone', 'Asia/Jakarta');
        $dnsServers = self::config($config, 'dns_servers', '8.8.8.8,1.1.1.1');
        $ntpServers = self::config($config, 'ntp_servers', '162.159.200.1,162.159.200.123');
        $radiusSrcAddress = self::config($config, 'radius_src_address', $router->public_ip ?: $router->management_ip);
        $incomingPort = self::config($config, 'radius_incoming_port', '3799');
        $poolName = self::config($config, 'pool_name', 'NEXPOOL');
        $poolComment = self::config($config, 'pool_comment', 'Network : 10.200.192.0/20');
        $poolRanges = self::config($config, 'pool_ranges', '10.200.192.100-10.200.207.254');
        $isolirPoolName = self::config($config, 'isolir_pool_name', 'NEXISOLIR');
        $isolirPoolComment = self::config($config, 'isolir_pool_comment', 'Network 10.200.208.0/23');
        $isolirPoolRanges = self::config($config, 'isolir_pool_ranges', '10.200.208.10-10.200.209.254');
        $profileName = self::config($config, 'ppp_profile_name', 'NEXRADIUS');
        $localAddress = self::config($config, 'ppp_local_address', '10.200.192.1');
        $isolirProfileName = self::config($config, 'isolir_profile_name', 'NEXISOLIR');
        $isolirLocalAddress = self::config($config, 'isolir_local_address', '10.200.208.1');
        $redirectHost = self::config($config, 'isolir_redirect_host', '103.253.27.164');
        $redirectPort = self::config($config, 'isolir_redirect_port', '3125');

        $radiusLine = $server
            ? '/radius add address='.$server->host.' comment="'.$snmpCommunity.'" authentication-port='.$server->auth_port.' accounting-port='.$server->acct_port.' secret="'.$server->shared_secret.'" service=ppp,login,hotspot src-address='.$radiusSrcAddress.' timeout=3s'
            : '# Radius server belum tersedia';

        return implode("\n", [
            '# NEXBIL Router Script',
            '# Router: '.$router->router_name.' / '.$router->hostname,
            '/system identity set name="'.$router->hostname.'"',
            '',
            '/snmp community',
            'set [ find default=yes ] disabled=yes write-access=no',
            'rem [find name!=public]',
            'add addresses='.$snmpAllowedAddress.' name='.$snmpCommunity.' write-access=yes',
            '/snmp set enabled=yes trap-community='.$snmpCommunity.' trap-version=2',
            '',
            '/system clock set time-zone-autodetect=no time-zone-name='.$timeZone,
            '/radius incoming set accept=yes port='.$incomingPort,
            '/ip dns set allow-remote-requests=yes servers='.$dnsServers,
            '/system ntp client servers rem [find]',
            '/system ntp client set enabled=yes servers='.$ntpServers,
            '',
            '/radius',
            'rem [find]',
            $radiusLine,
            '/radius set require-message-auth=no num=0',
            '',
            '/ip pool',
            'add comment="'.$poolComment.'" name='.$poolName.' ranges='.$poolRanges,
            'add comment="'.$isolirPoolComment.'" name='.$isolirPoolName.' ranges='.$isolirPoolRanges,
            '',
            '/ppp profile',
            'add insert-queue-before=first local-address='.$localAddress.' name='.$profileName.' only-one=yes remote-address='.$poolName,
            'add insert-queue-before=first local-address='.$isolirLocalAddress.' name='.$isolirProfileName.' comment="default by NEXBIL (jangan dirubah)" only-one=yes remote-address='.$isolirPoolName,
            '',
            '/ppp aaa set use-radius=yes accounting=yes interim-update=5m',
            '',
            '# NAT redirect isolir ke webproxy',
            '/ip firewall nat rem [find src-address-list~"NEX"]',
            '/ip firewall nat add action=redirect chain=dstnat comment="NEXISOLIR" dst-address=!'.$redirectHost.' dst-port=80,443,8080 protocol=tcp src-address-list='.$isolirPoolName.' to-ports='.$redirectPort,
            '/ip firewall filter rem [find src-address-list~"NEX"]',
            '/ip firewall filter add action=reject chain=forward comment=NEXISOLIR dst-address=!'.$redirectHost.' protocol=tcp reject-with=icmp-network-unreachable src-address-list='.$isolirPoolName,
            '/ip firewall filter add action=reject chain=forward comment=NEXISOLIR dst-address=!'.$redirectHost.' dst-port=!53,5353 protocol=udp reject-with=icmp-network-unreachable src-address-list='.$isolirPoolName,
            '',
            '/ip hotspot profile set [find] use-radius=yes radius-accounting=yes radius-interim-update=5m',
            '/ip hotspot user profile rem [find name='.$profileName.']',
            '/ip hotspot user profile set [find default=yes] insert-queue-before=first parent-queue=*8',
            '/ip hotspot user profile add insert-queue-before=first keepalive-timeout=10m mac-cookie-timeout=1w name='.$profileName.' shared-users=unlimited transparent-proxy=yes open-status-page=always status-autorefresh=10m',
            '',
            '/ip proxy set cache-administrator=webmaster@NEXradius.com enabled=yes max-cache-object-size=1KiB max-cache-size=none max-client-connections=50 max-fresh-time=5m max-server-connections=50 port='.$redirectPort,
            '/ip proxy access rem [find]',
            'add action=redirect action-data=http://'.$redirectHost.' src-address='.$isolirPoolRanges,
        ]);
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function config(array $config, string $key, string $default): string
    {
        $value = $config[$key] ?? null;

        return blank($value) ? $default : (string) $value;
    }
}
