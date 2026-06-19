<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Service';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Profile Langganan';

    protected static ?string $modelLabel = 'Profile Berlangganan';

    protected static ?string $pluralModelLabel = 'Profil Langganan';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\Select::make('service_category_id')->options(fn () => AdminOptions::serviceCategories())->searchable(),
                Forms\Components\TextInput::make('name')->label('Nama profile')->required()->maxLength(255),
                Forms\Components\TextInput::make('sku')->label('Kode profile')->required()->maxLength(255),
                Forms\Components\TextInput::make('mikrotik_group')
                    ->label('Mikrotik group')
                    ->default('RLRADIUS')
                    ->helperText('Harus sama dengan nama profile di mikrotik')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('mikrotik_rate_limit')
                    ->label('Mikrotik rate limit')
                    ->placeholder('1500k/2M 0/0 0/0 0/0 8 0/0')
                    ->helperText('Jika dikosongkan, maka akan digunakan limitasi profile mikrotik')
                    ->maxLength(255),
                Forms\Components\TextInput::make('shared_users')->label('Shared')->numeric()->default(1)->required(),
                Forms\Components\TextInput::make('active_days')->label('Masa aktif (Hari)')->numeric()->default(30)->required(),
                Forms\Components\TextInput::make('hpp')->label('HPP')->numeric()->prefix('Rp')->default(0),
                Forms\Components\TextInput::make('commission')
                    ->label('Komisi')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->helperText('Komisi reseller yang akan dikeluarkan tiap pembayaran. Isi 0 jika hitungan komisi dalam bentuk persentase.'),
                Forms\Components\TextInput::make('price')->label('Harga')->numeric()->prefix('Rp')->helperText('Harga diluar PPN')->required(),
                Forms\Components\Select::make('billing_cycle')
                    ->options(['monthly' => 'Monthly', 'one_time' => 'One time'])
                    ->default('monthly')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                    ->default('active')
                    ->required(),
                Forms\Components\KeyValue::make('pricing')->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('hpp')->label('HPP')->numeric(decimalPlaces: 0)->sortable(),
                Tables\Columns\TextColumn::make('commission')->label('Komisi')->numeric(decimalPlaces: 0)->sortable(),
                Tables\Columns\TextColumn::make('price')->label('Harga')->numeric(decimalPlaces: 0)->sortable(),
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
}
