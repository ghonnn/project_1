<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\Product;
use App\Models\Service;
use App\Models\Router;
use App\Services\ServiceProvisioningService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationGroup = 'Langganan';

    protected static ?string $navigationLabel = 'Data Berlangganan';

    protected static ?string $modelLabel = 'Data Berlangganan';

    protected static ?string $pluralModelLabel = 'Data Berlangganan';

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Section::make('Data Berlangganan')
                    ->columns(1)
                    ->schema([
                        Forms\Components\TextInput::make('cid')
                            ->label('No. CID / Layanan')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Otomatis saat disimpan'),
                        Forms\Components\Select::make('tenant_id')
                            ->options(fn () => AdminOptions::tenants())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('customer_id', null)),
                        Forms\Components\Select::make('customer_id')
                            ->label('Nama Pelanggan')
                            ->options(fn (Forms\Get $get) => AdminOptions::customers($get('tenant_id')))
                            ->getSearchResultsUsing(fn (string $search, Forms\Get $get): array => AdminOptions::customers($get('tenant_id'), $search))
                            ->getOptionLabelUsing(fn (?string $value): ?string => AdminOptions::customerOptionLabel($value))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('region')->label('Wilayah')->maxLength(255),
                        Forms\Components\TextInput::make('latitude')->label('Latitude')->numeric(),
                        Forms\Components\TextInput::make('longitude')->label('Longitude')->numeric(),
                        Forms\Components\Placeholder::make('map_link')
                            ->label('Map')
                            ->content(fn (?Service $record): HtmlString|string => $record?->latitude && $record?->longitude
                                ? new HtmlString('<a class="text-primary-600 underline" href="https://www.google.com/maps?q='.$record->latitude.','.$record->longitude.'" target="_blank" rel="noopener">Buka Google Maps</a>')
                                : 'Isi latitude dan longitude, lalu simpan untuk mendapatkan link map.'),
                        Forms\Components\Textarea::make('installation_address')->label('Alamat Pemasangan')->rows(3),
                    ]),
                Forms\Components\Section::make('Internet Hardware')
                    ->columns(1)
                    ->schema([
                        Forms\Components\TextInput::make('partner_name')->label('Nama Partner')->maxLength(255),
                        Forms\Components\Select::make('product_id')
                            ->label('Profile / Produk')
                            ->options(fn () => AdminOptions::products())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                $product = $state ? Product::query()->find($state) : null;

                                if (! $product) {
                                    return;
                                }

                                $set('service_category_id', $product->service_category_id);
                                $set('billing_profile_name', $product->name);
                                $set('billing_cycle', $product->billing_cycle);
                                $set('profile_price', $product->price);
                            }),
                        Forms\Components\Select::make('service_category_id')->label('Kategori Layanan')->options(fn () => AdminOptions::serviceCategories())->searchable(),
                        Forms\Components\TextInput::make('server_name')->label('Server')->maxLength(255),
                        Forms\Components\Select::make('connection_type')
                            ->label('Kategori Koneksi')
                            ->options(['PPP' => 'PPP', 'DHCP' => 'DHCP', 'HOTSPOT' => 'HOTSPOT']),
                        Forms\Components\TextInput::make('internet_username')->label('Username Internet')->maxLength(255),
                        Forms\Components\TextInput::make('internet_password')->label('Password Internet')->password()->revealable()->maxLength(255),
                        Forms\Components\TextInput::make('ip_address')->label('IP Address')->placeholder('Kosongkan jika dynamic')->maxLength(255),
                        Forms\Components\Select::make('device_ownership_status')
                            ->label('Status Perangkat')
                            ->options(['dipinjamkan' => 'Dipinjamkan', 'beli' => 'Beli']),
                        Forms\Components\TextInput::make('device_brand')->label('Merk Perangkat')->maxLength(255),
                        Forms\Components\TextInput::make('device_serial_number')->label('SN Perangkat')->maxLength(255),
                        Forms\Components\TextInput::make('odp_number')->label('ODP Nomor')->maxLength(255),
                        Forms\Components\TextInput::make('odp_port')->label('No. Port ODP')->maxLength(255),
                        Forms\Components\TextInput::make('onu_slot')->label('Slot ONU')->placeholder('gpon-onu_1/1/1:1')->maxLength(255),
                    ]),
                Forms\Components\Section::make('Billing')
                    ->columns(1)
                    ->schema([
                        Forms\Components\TextInput::make('billing_profile_name')->label('Profile Sedang Digunakan')->maxLength(255),
                        Forms\Components\TextInput::make('billing_cycle')->label('Siklus Tagihan')->placeholder('Siklus bulan')->maxLength(255),
                        Forms\Components\Select::make('billing_type')
                            ->label('Jenis Tagihan')
                            ->options(['prabayar' => 'Prabayar', 'pascabayar' => 'Pascabayar']),
                        Forms\Components\DatePicker::make('billing_active_date')->label('Tanggal Aktif'),
                        Forms\Components\DatePicker::make('billing_isolation_date')->label('Tanggal Isolir'),
                        Forms\Components\Toggle::make('ppn_enabled')->label('PPN 11%'),
                        Forms\Components\TextInput::make('unit_code')->label('Kode Unit')->maxLength(255),
                        Forms\Components\TextInput::make('profile_price')->label('Harga Profile')->numeric()->prefix('Rp'),
                        Forms\Components\TextInput::make('partner_commission')->label('Komisi Partner')->numeric()->prefix('Rp'),
                    ]),
                Forms\Components\Section::make('Lainnya')
                    ->columns(1)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(['requested' => 'Requested', 'active' => 'Active', 'suspended' => 'Suspended', 'terminated' => 'Terminated'])
                            ->default('requested')
                            ->required(),
                        Forms\Components\DatePicker::make('installed_at')->label('Tanggal Pasang'),
                        Forms\Components\DateTimePicker::make('activated_at')->label('Tanggal Aktif Sistem'),
                        Forms\Components\DateTimePicker::make('suspended_at')->label('Tanggal Suspend'),
                        Forms\Components\DateTimePicker::make('terminated_at')->label('Tanggal Terminate'),
                        Forms\Components\Placeholder::make('created_at')->label('Tanggal Input')->content(fn (?Service $record): string => $record?->created_at?->format('Y-m-d H:i:s') ?? '-'),
                        Forms\Components\Placeholder::make('updated_at')->label('Log Terakhir Diubah')->content(fn (?Service $record): string => $record?->updated_at?->format('Y-m-d H:i:s') ?? '-'),
                        Forms\Components\Textarea::make('notes')->label('Catatan')->rows(4),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('connection_type')->label('INET')->badge(),
                Tables\Columns\TextColumn::make('cid')->label('NOLAYANAN')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->label('PELANGGAN')->searchable(),
                Tables\Columns\TextColumn::make('serviceCategory.name')->label('KTG')->toggleable(),
                Tables\Columns\TextColumn::make('billing_profile_name')->label('PROFILE')->searchable(),
                Tables\Columns\TextColumn::make('billing_type')->label('JNS TAGIHAN')->badge(),
                Tables\Columns\TextColumn::make('billing_cycle')->label('SIKLUS')->searchable(),
                Tables\Columns\TextColumn::make('billing_active_date')->label('TGL AKTIF')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('billing_isolation_date')->label('TGL ISOLIR')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('internet_username')->label('USERNAME')->searchable(),
                Tables\Columns\TextColumn::make('internet_password')
                    ->label('PASSWORD')
                    ->formatStateUsing(fn (?string $state): string => $state ? str_repeat('*', min(strlen($state), 10)) : '-'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'requested' => 'info',
                    'suspended' => 'warning',
                    'terminated' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('activated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('partner_name')
                    ->label('Cari mitra')
                    ->options(fn () => Service::query()->whereNotNull('partner_name')->distinct()->orderBy('partner_name')->pluck('partner_name', 'partner_name')->all()),
                Tables\Filters\SelectFilter::make('region')
                    ->label('Cari wilayah')
                    ->options(fn () => Service::query()->whereNotNull('region')->distinct()->orderBy('region')->pluck('region', 'region')->all()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('All Data')
                    ->options(['requested' => 'Requested', 'active' => 'Aktif', 'suspended' => 'Terisolir', 'terminated' => 'Stop']),
                Tables\Filters\SelectFilter::make('router_id')
                    ->label('All Router')
                    ->options(fn () => Router::query()->orderBy('router_name')->pluck('router_name', 'id')->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, string $routerId): Builder => $query->whereHas('routerMappings', fn (Builder $query): Builder => $query->where('router_id', $routerId))
                    )),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('All Profile')
                    ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id')->all()),
            ])
            ->actions([
                Tables\Actions\Action::make('provision')
                    ->label('Hubungkan')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->modalHeading(fn (Service $record): string => 'Hubungkan Layanan '.$record->cid)
                    ->form(fn (Service $record): array => [
                        Forms\Components\Select::make('router_id')
                            ->label('Router')
                            ->options(fn () => AdminOptions::routers())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('interface_id', null)),
                        Forms\Components\Select::make('interface_id')
                            ->label('Interface Router')
                            ->options(fn (Forms\Get $get) => AdminOptions::routerInterfaces($get('router_id')))
                            ->searchable()
                            ->disabled(fn (Forms\Get $get): bool => blank($get('router_id'))),
                        Forms\Components\TextInput::make('vlan_id')
                            ->label('VLAN ID')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(4094),
                        Forms\Components\TextInput::make('username')
                            ->label('Username Internet')
                            ->default(fn () => $record->internet_username ?: $record->cid)
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->label('Password Internet')
                            ->default(fn () => $record->internet_password ?: strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)))
                            ->required(),
                        Forms\Components\Toggle::make('create_invoice')
                            ->label('Buat invoice awal')
                            ->default(true),
                    ])
                    ->action(function (Service $record, array $data): void {
                        $result = app(ServiceProvisioningService::class)->provision($record, $data);

                        Notification::make()
                            ->title('Pelanggan berhasil terhubung')
                            ->body('Radius user tersinkron dan layanan aktif'.($result['invoice'] ? ', invoice awal dibuat.' : '.'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
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
            RelationManagers\PlanChangesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
