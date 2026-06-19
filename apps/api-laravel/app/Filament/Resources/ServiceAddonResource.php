<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceAddonResource\Pages;
use App\Filament\Support\AdminOptions;
use App\Models\Service;
use App\Models\ServiceAddon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceAddonResource extends Resource
{
    protected static ?string $model = ServiceAddon::class;

    protected static ?string $navigationGroup = 'Tagihan';

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?string $navigationLabel = 'Addons Bulanan';

    protected static ?string $modelLabel = 'Addon Bulanan';

    protected static ?string $pluralModelLabel = 'Addons Bulanan';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\Select::make('service_id')
                    ->options(fn () => AdminOptions::services())
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                        $service = $state ? Service::query()->find($state) : null;

                        if ($service) {
                            $set('tenant_id', $service->tenant_id);
                        }
                    }),
                Forms\Components\TextInput::make('name')->label('Nama Item')->required()->maxLength(255),
                Forms\Components\TextInput::make('quantity')->label('Qty')->numeric()->default(1)->required(),
                Forms\Components\TextInput::make('unit_price')->label('Harga Satuan')->numeric()->prefix('Rp')->default(0)->required(),
                Forms\Components\TextInput::make('monthly_amount')->label('Total Bulanan Non Prorata')->numeric()->prefix('Rp')->disabled()->dehydrated(false),
                Forms\Components\Select::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive'])->default('active')->required(),
                Forms\Components\Textarea::make('notes')->label('Catatan')->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service.cid')->label('CID')->searchable(),
                Tables\Columns\TextColumn::make('service.customer.name')->label('Pelanggan')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Item')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->label('Qty'),
                Tables\Columns\TextColumn::make('unit_price')->label('Harga')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('monthly_amount')->label('Bulanan')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceAddons::route('/'),
            'create' => Pages\CreateServiceAddon::route('/create'),
            'edit' => Pages\EditServiceAddon::route('/{record}/edit'),
        ];
    }
}
