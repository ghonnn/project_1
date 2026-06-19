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
                Forms\Components\KeyValue::make('snmp_profile')->label('Profile SNMP')->columnSpanFull(),
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

                        $script = implode("\n", [
                            '# NEXBIL Router Basic Script',
                            '# Router: '.$record->router_name,
                            '/system identity set name="'.$record->hostname.'"',
                            $server ? '/radius add service=ppp,hotspot address='.$server->host.' secret="'.$server->shared_secret.'" authentication-port='.$server->auth_port.' accounting-port='.$server->acct_port.' timeout=300ms' : '# Radius server belum tersedia',
                            '/ppp aaa set use-radius=yes accounting=yes interim-update=5m',
                            '/ip hotspot profile set [ find default=yes ] use-radius=yes',
                        ]);

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
}
