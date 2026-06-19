<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\Product;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationGroup = 'Layanan';

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
                        Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable()->required()->live(),
                        Forms\Components\Select::make('customer_id')->label('Nama Pelanggan')->options(fn () => AdminOptions::customers())->searchable()->required(),
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
                        Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->searchable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('cid')->label('CID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.phone')->label('Phone')->searchable(),
                Tables\Columns\TextColumn::make('region')->label('Wilayah')->searchable(),
                Tables\Columns\TextColumn::make('connection_type')->label('Kategori')->badge(),
                Tables\Columns\TextColumn::make('routerMappings.router.router_name')->label('Router')->listWithLineBreaks()->bulleted(),
                Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable(),
                Tables\Columns\TextColumn::make('profile_price')->label('Harga')->money('IDR')->sortable(),
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
                //
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
            //
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
