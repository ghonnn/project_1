<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\Product;
use App\Models\Service;
use App\Models\Router;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
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
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('cid')
                            ->label('No. CID / Layanan')
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(32)
                            ->placeholder('Otomatis saat disimpan'),
                        Forms\Components\Select::make('tenant_id')
                            ->options(fn () => AdminOptions::tenants())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state): void {
                                $set('customer_id', null);
                                self::refreshBillingDates($set, $state, $get('billing_active_date'));
                                self::refreshPpnFromSettings($set, $state, $get('product_id'));
                            }),
                        Forms\Components\Select::make('customer_id')
                            ->label('Nama Pelanggan')
                            ->options(fn (Forms\Get $get) => AdminOptions::customers($get('tenant_id')))
                            ->getSearchResultsUsing(fn (string $search, Forms\Get $get): array => AdminOptions::customers($get('tenant_id'), $search))
                            ->getOptionLabelUsing(fn (?string $value): ?string => AdminOptions::customerOptionLabel($value))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('region')->label('Wilayah')->maxLength(80),
                        Forms\Components\TextInput::make('latitude')->label('Latitude')->numeric()->maxLength(16),
                        Forms\Components\TextInput::make('longitude')->label('Longitude')->numeric()->maxLength(16),
                        Forms\Components\Placeholder::make('map_link')
                            ->label('Map')
                            ->content(fn (?Service $record): HtmlString|string => $record?->latitude && $record?->longitude
                                ? new HtmlString('<a class="text-primary-600 underline" href="https://www.google.com/maps?q='.$record->latitude.','.$record->longitude.'" target="_blank" rel="noopener">Buka Google Maps</a>')
                                : 'Isi latitude dan longitude, lalu simpan untuk mendapatkan link map.')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('installation_address')->label('Alamat Pemasangan')->rows(3)->maxLength(500)->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Internet Hardware')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('partner_name')->label('Nama Partner')->maxLength(80),
                        Forms\Components\Select::make('product_id')
                            ->label('Profile / Produk')
                            ->options(fn (Forms\Get $get) => AdminOptions::products($get('tenant_id')))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state): void {
                                self::applyProductBilling($set, $get('tenant_id'), $state);
                            }),
                        Forms\Components\Select::make('service_category_id')->label('Kategori Layanan')->options(fn (Forms\Get $get) => AdminOptions::serviceCategories($get('tenant_id')))->searchable(),
                        Forms\Components\TextInput::make('server_name')->label('Server')->maxLength(80),
                        Forms\Components\Select::make('connection_type')
                            ->label('Kategori Koneksi')
                            ->options(['PPP' => 'PPP', 'DHCP' => 'DHCP', 'HOTSPOT' => 'HOTSPOT']),
                        Forms\Components\TextInput::make('internet_username')->label('Username Internet')->maxLength(64),
                        Forms\Components\TextInput::make('internet_password')->label('Password Internet')->password()->revealable()->maxLength(64),
                        Forms\Components\TextInput::make('ip_address')->label('IP Address')->placeholder('Kosongkan jika dynamic')->maxLength(45),
                        Forms\Components\Select::make('device_ownership_status')
                            ->label('Status Perangkat')
                            ->options(['dipinjamkan' => 'Dipinjamkan', 'beli' => 'Beli']),
                        Forms\Components\TextInput::make('device_brand')->label('Merk Perangkat')->maxLength(80),
                        Forms\Components\TextInput::make('device_serial_number')->label('SN Perangkat')->maxLength(80),
                        Forms\Components\TextInput::make('odp_number')->label('ODP Nomor')->maxLength(50),
                        Forms\Components\TextInput::make('odp_port')->label('No. Port ODP')->maxLength(20),
                        Forms\Components\TextInput::make('onu_slot')->label('Slot ONU')->placeholder('gpon-onu_1/1/1:1')->maxLength(50),
                    ]),
                Forms\Components\Section::make('Hubungkan Router')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('provision_router_id')
                            ->label('Router')
                            ->options(fn (Forms\Get $get) => AdminOptions::routers($get('tenant_id')))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('provision_interface_id', null))
                            ->default(fn (?Service $record): ?string => $record?->routerMappings()->where('is_primary', true)->value('router_id')),
                        Forms\Components\Select::make('provision_interface_id')
                            ->label('Interface Router')
                            ->options(fn (Forms\Get $get) => AdminOptions::routerInterfaces($get('provision_router_id')))
                            ->searchable()
                            ->disabled(fn (Forms\Get $get): bool => blank($get('provision_router_id')))
                            ->default(fn (?Service $record): ?string => $record?->routerMappings()->where('is_primary', true)->value('interface_id')),
                        Forms\Components\TextInput::make('provision_vlan_id')
                            ->label('VLAN ID')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(4094)
                            ->maxLength(4)
                            ->default(fn (?Service $record): ?int => $record?->routerMappings()->where('is_primary', true)->value('vlan_id')),
                        Forms\Components\TextInput::make('provision_username')
                            ->label('Username Internet')
                            ->maxLength(64)
                            ->default(fn (?Service $record): ?string => $record?->internet_username),
                        Forms\Components\TextInput::make('provision_password')
                            ->label('Password Internet')
                            ->password()
                            ->revealable()
                            ->maxLength(64)
                            ->default(fn (?Service $record): ?string => $record?->internet_password),
                        Forms\Components\Toggle::make('provision_create_invoice')
                            ->label('Buat invoice awal')
                            ->default(fn (?Service $record): bool => ! $record?->exists),
                    ]),
                Forms\Components\Section::make('Billing')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('billing_profile_name')->label('Profile Sedang Digunakan')->maxLength(80),
                        Forms\Components\TextInput::make('billing_cycle')->label('Siklus Tagihan')->placeholder('Siklus bulan')->maxLength(30),
                        Forms\Components\Select::make('billing_type')
                            ->label('Jenis Tagihan')
                            ->options(['prabayar' => 'Prabayar', 'pascabayar' => 'Pascabayar']),
                        Forms\Components\DatePicker::make('billing_active_date')
                            ->label('Tanggal Aktif')
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get, mixed $state) => self::refreshBillingDates($set, $get('tenant_id'), $state)),
                        Forms\Components\DatePicker::make('billing_isolation_date')
                            ->label('Tanggal Isolir')
                            ->helperText('Otomatis mengikuti Setting Billing Langganan: Tanggal Isolir SIKLUS BULAN.'),
                        Forms\Components\DatePicker::make('invoice_issue_date')
                            ->label('Tanggal Terbit Invoice')
                            ->helperText('Otomatis mengikuti kolom Terbit invoice pada Setting Billing Langganan.'),
                        Forms\Components\Toggle::make('ppn_enabled')
                            ->label('PPN')
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get, mixed $state) => self::refreshServicePrice($set, $get('dpp_amount'), $get('ppn_rate'), (bool) $state)),
                        Forms\Components\TextInput::make('unit_code')->label('Kode Unit')->maxLength(50),
                        self::rupiahInput('dpp_amount', 'Harga Dasar / DPP')
                            ->helperText('Dasar pengenaan pajak sebelum PPN.')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get, mixed $state) => self::refreshServicePrice($set, $state, $get('ppn_rate'), (bool) $get('ppn_enabled'))),
                        Forms\Components\TextInput::make('ppn_rate')
                            ->label('Tarif PPN')
                            ->numeric()
                            ->suffix('%')
                            ->default(11)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get, mixed $state) => self::refreshServicePrice($set, $get('dpp_amount'), $state, (bool) $get('ppn_enabled'))),
                        self::rupiahInput('profile_price', 'Harga Profile')
                            ->helperText('Harga final pelanggan, otomatis dari DPP dan tarif PPN.')
                            ->readOnly(),
                        self::rupiahInput('partner_commission', 'Komisi Partner'),
                    ]),
                Forms\Components\Section::make('Lainnya')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(['requested' => 'Requested', 'active' => 'Active', 'suspended' => 'Suspended', 'terminated' => 'Terminated'])
                            ->default('requested')
                            ->required(),
                        Forms\Components\DatePicker::make('installed_at')->label('Tanggal Pasang'),
                        Forms\Components\DatePicker::make('activated_at')->label('Tanggal Aktif Sistem'),
                        Forms\Components\DatePicker::make('suspended_at')->label('Tanggal Suspend'),
                        Forms\Components\DatePicker::make('terminated_at')->label('Tanggal Terminate'),
                        Forms\Components\Placeholder::make('created_at')->label('Tanggal Input')->content(fn (?Service $record): string => $record?->created_at?->format('Y-m-d H:i:s') ?? '-'),
                        Forms\Components\Placeholder::make('updated_at')->label('Log Terakhir Diubah')->content(fn (?Service $record): string => $record?->updated_at?->format('Y-m-d H:i:s') ?? '-'),
                        Forms\Components\Textarea::make('notes')->label('Catatan')->rows(4)->maxLength(1000)->columnSpanFull(),
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

    private static function rupiahInput(string $name, string $label): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make($name)
            ->label($label)
            ->prefix('Rp')
            ->inputMode('numeric')
            ->mask(RawJs::make(<<<'JS'
                $input.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')
            JS))
            ->formatStateUsing(fn ($state) => self::formatRupiah($state))
            ->dehydrateStateUsing(fn ($state) => self::parseRupiah($state));
    }

    private static function formatRupiah(mixed $state): string
    {
        if ($state === null || $state === '') {
            return '0';
        }

        return number_format((float) $state, 0, ',', '.');
    }

    private static function parseRupiah(mixed $state): float
    {
        $value = trim((string) $state);

        if ($value === '') {
            return 0;
        }

        $value = str_replace(['Rp', ' '], '', $value);

        if (str_contains($value, ',')) {
            return (float) str_replace(',', '.', str_replace('.', '', $value));
        }

        if (preg_match('/^\d+\.\d{2}$/', $value) === 1) {
            return (float) $value;
        }

        return (float) str_replace('.', '', $value);
    }

    private static function applyProductBilling(Forms\Set $set, ?string $tenantId, ?string $productId): void
    {
        $product = $productId ? Product::query()->find($productId) : null;

        if (! $product) {
            return;
        }

        $settings = self::billingSettings($tenantId ?: $product->tenant_id);
        $ppnEnabled = self::resolvePpnEnabled($settings, $product);
        $ppnRate = (float) ($settings['ppn_rate'] ?? 11);
        $dpp = (float) ($product->hpp ?: $product->price);

        $set('service_category_id', $product->service_category_id);
        $set('billing_profile_name', $product->name);
        $set('billing_cycle', $product->billing_cycle);
        $set('ppn_enabled', $ppnEnabled);
        $set('ppn_rate', $ppnRate);
        $set('dpp_amount', self::formatRupiah($dpp));
        self::refreshServicePrice($set, $dpp, $ppnRate, $ppnEnabled);
    }

    private static function refreshPpnFromSettings(Forms\Set $set, ?string $tenantId, ?string $productId): void
    {
        if (! $productId) {
            return;
        }

        self::applyProductBilling($set, $tenantId, $productId);
    }

    private static function refreshServicePrice(Forms\Set $set, mixed $dpp, mixed $ppnRate, bool $ppnEnabled): void
    {
        $base = self::parseRupiah($dpp);
        $rate = (float) ($ppnRate ?: 0);
        $price = $ppnEnabled ? round($base + ($base * ($rate / 100))) : $base;

        $set('profile_price', self::formatRupiah($price));
    }

    private static function refreshBillingDates(Forms\Set $set, ?string $tenantId, mixed $activeDate): void
    {
        $settings = self::billingSettings($tenantId);
        $active = self::dateFromState($activeDate) ?: now();
        $isolationDay = max(1, min(31, (int) ($settings['monthly_isolation_day'] ?? 15)));
        $publishBeforeDays = max(0, (int) ($settings['invoice_publish_day'] ?? 10));

        $isolationDate = $active->copy()->day(min($isolationDay, $active->daysInMonth));

        if ($isolationDate->lt($active->copy()->startOfDay())) {
            $nextMonth = $active->copy()->addMonthNoOverflow();
            $isolationDate = $nextMonth->copy()->day(min($isolationDay, $nextMonth->daysInMonth));
        }

        $invoiceIssueDate = $isolationDate->copy()->subDays($publishBeforeDays);

        $set('billing_isolation_date', $isolationDate->toDateString());
        $set('suspended_at', $isolationDate->toDateString());
        $set('invoice_issue_date', $invoiceIssueDate->toDateString());
    }

    private static function billingSettings(?string $tenantId): array
    {
        $tenant = $tenantId
            ? Tenant::query()->find($tenantId)
            : Tenant::query()->orderBy('name')->first();

        return $tenant?->billing_settings ?? [];
    }

    private static function resolvePpnEnabled(array $settings, Product $product): bool
    {
        return match ($settings['ppn_rule'] ?? 'optional') {
            'all_taxed' => true,
            'all_untaxed' => false,
            default => (bool) $product->ppn_enabled,
        };
    }

    private static function dateFromState(mixed $state): ?Carbon
    {
        if ($state instanceof Carbon) {
            return $state->copy();
        }

        if (! $state) {
            return null;
        }

        return Carbon::parse($state);
    }
}
