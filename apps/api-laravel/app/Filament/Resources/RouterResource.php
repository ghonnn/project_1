<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RouterResource\Pages;
use App\Filament\Resources\RouterResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\Router;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RouterResource extends Resource
{
    protected static ?string $model = Router::class;

    protected static ?string $navigationGroup = 'Network';

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
                Forms\Components\TextInput::make('vendor')->maxLength(255),
                Forms\Components\TextInput::make('model')->maxLength(255),
                Forms\Components\TextInput::make('serial_number')->maxLength(255),
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
                Forms\Components\TextInput::make('site_name')->maxLength(255),
                Forms\Components\TextInput::make('public_ip')->maxLength(255),
                Forms\Components\TextInput::make('online_sessions')->label('Online')->numeric()->default(0),
                Forms\Components\TextInput::make('latitude')->numeric(),
                Forms\Components\TextInput::make('longitude')->numeric(),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'active' => 'Active', 'maintenance' => 'Maintenance', 'inactive' => 'Inactive'])
                    ->default('draft')
                    ->required(),
                Forms\Components\TextInput::make('snmp_status')->default('not_configured')->maxLength(255),
                Forms\Components\KeyValue::make('snmp_profile')->columnSpanFull(),
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
                        'reachable' => 'Connected',
                        default => 'Disconnected',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'reachable' ? 'success' : 'gray'),
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
                Tables\Actions\Action::make('download_script')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (Router $record) {
                        $script = implode("\n", [
                            '# NEXBIL Router Basic Script',
                            '/system identity set name="'.$record->hostname.'"',
                            $record->radius_secret ? '/radius add service=ppp address='.$record->management_ip.' secret="'.$record->radius_secret.'" authentication-port=1812 accounting-port=1813 timeout=300ms' : '# Radius secret belum diisi',
                            '/ppp aaa set use-radius=yes accounting=yes interim-update=5m',
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
            //
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
}
