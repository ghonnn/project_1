<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Langganan';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Profile Langganan';

    protected static ?string $modelLabel = 'Profile Berlangganan';

    protected static ?string $pluralModelLabel = 'Profil Langganan';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\Select::make('service_category_id')->options(fn () => AdminOptions::serviceCategories())->searchable(),
                Forms\Components\TextInput::make('name')->label('Nama profile')->required()->maxLength(80),
                Forms\Components\TextInput::make('sku')
                    ->label('Kode profile')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, mixed $state): void {
                        if (blank($get('mikrotik_group')) || in_array($get('mikrotik_group'), ['RLRADIUS', 'NEX-PROFILE'], true)) {
                            $set('mikrotik_group', self::radiusGroupName((string) $state));
                        }
                    })
                    ->maxLength(50),
                Forms\Components\TextInput::make('mikrotik_group')
                    ->label('Mikrotik group')
                    ->default('NEX-PROFILE')
                    ->helperText('Dipakai sebagai group FreeRadius/MikroTik. Buat unik per paket agar rate limit tidak saling menimpa.')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('mikrotik_rate_limit')
                    ->label('Mikrotik rate limit')
                    ->placeholder('1500k/2M 0/0 0/0 0/0 8 0/0')
                    ->helperText('Jika dikosongkan, maka akan digunakan limitasi profile mikrotik')
                    ->maxLength(120),
                Forms\Components\TextInput::make('shared_users')->label('Shared')->numeric()->default(1)->required(),
                Forms\Components\TextInput::make('active_days')->label('Masa aktif (Hari)')->numeric()->default(30)->required(),
                self::rupiahInput('hpp', 'HPP')
                    ->helperText('Harga sebelum PPN 11%.')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get, mixed $state) => self::refreshPrice($set, $state, $get('ppn_enabled')))
                    ->default(0),
                self::rupiahInput('commission', 'Komisi')
                    ->default(0)
                    ->helperText('Komisi reseller yang akan dikeluarkan tiap pembayaran. Isi 0 jika hitungan komisi dalam bentuk persentase.'),
                Forms\Components\Toggle::make('ppn_enabled')
                    ->label('Kenakan PPN 11%')
                    ->helperText('Jika aktif, harga otomatis menjadi HPP + 11%.')
                    ->live()
                    ->default(false)
                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get, mixed $state) => self::refreshPrice($set, $get('hpp'), $state)),
                self::rupiahInput('price', 'Harga')
                    ->helperText('Harga final pelanggan. Otomatis mengikuti HPP dan opsi PPN.')
                    ->default(0)
                    ->readOnly()
                    ->dehydrated()
                    ->required(),
                Forms\Components\Select::make('billing_cycle')
                    ->label('Siklus Tagihan')
                    ->options(['monthly' => 'Bulanan', 'one_time' => 'Sekali Bayar'])
                    ->default('monthly')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(['active' => 'Aktif', 'inactive' => 'Non Aktif'])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Profil Langganan')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Profile')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('mikrotik_group')->label('Group')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('mikrotik_rate_limit')->label('Rate Limit')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('shared_users')->label('Shared')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('active_days')->label('Aktif')->suffix(' Hari')->sortable(),
                Tables\Columns\TextColumn::make('hpp')->label('HPP')->formatStateUsing(fn ($state) => self::formatRupiah($state))->sortable(),
                Tables\Columns\IconColumn::make('ppn_enabled')->label('PPN')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('commission')->label('Komisi')->formatStateUsing(fn ($state) => self::formatRupiah($state))->sortable(),
                Tables\Columns\TextColumn::make('price')->label('Harga')->formatStateUsing(fn ($state) => self::formatRupiah($state))->sortable(),
                Tables\Columns\TextColumn::make('services_count')->label('Pelanggan')->counts('services')->badge()->color('info'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    default => 'gray',
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Aktif', 'inactive' => 'Non Aktif'])
                    ->default('active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('set_active')
                        ->label('Set Aktif')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'active']))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('set_inactive')
                        ->label('Non Aktif')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => 'inactive']))
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
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

    private static function refreshPrice(Forms\Set $set, mixed $hpp, mixed $ppnEnabled): void
    {
        $set('price', self::formatRupiah(self::calculatePrice($hpp, (bool) $ppnEnabled)));
    }

    private static function calculatePrice(mixed $hpp, bool $ppnEnabled): float
    {
        $basePrice = self::parseRupiah($hpp);

        return $ppnEnabled ? round($basePrice * 1.11) : $basePrice;
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

    private static function radiusGroupName(string $value): string
    {
        $value = strtoupper(preg_replace('/[^A-Za-z0-9_-]+/', '-', trim($value)) ?: 'NEX-PROFILE');

        return substr($value, 0, 50);
    }
}
