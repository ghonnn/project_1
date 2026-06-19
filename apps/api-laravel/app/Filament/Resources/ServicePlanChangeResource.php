<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServicePlanChangeResource\Pages;
use App\Filament\Support\AdminOptions;
use App\Models\Service;
use App\Models\ServicePlanChange;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ServicePlanChangeResource extends Resource
{
    protected static ?string $model = ServicePlanChange::class;

    protected static ?string $navigationGroup = 'Layanan';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Naik / Turun Paket';

    protected static ?string $modelLabel = 'Naik / Turun Paket';

    protected static ?string $pluralModelLabel = 'Naik / Turun Paket';

    protected static ?int $navigationSort = 20;

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

                        if (! $service) {
                            return;
                        }

                        $set('tenant_id', $service->tenant_id);
                        $set('old_product_id', $service->product_id);
                    }),
                Forms\Components\DatePicker::make('change_date')->label('Tanggal')->default(now())->required(),
                Forms\Components\Select::make('old_product_id')->label('Profile Awal')->options(fn () => AdminOptions::products())->searchable(),
                Forms\Components\Select::make('new_product_id')->label('Profile Baru')->options(fn () => AdminOptions::products())->searchable()->required(),
                Forms\Components\Select::make('admin_user_id')->label('Admin')->options(fn () => AdminOptions::users())->default(fn () => Auth::id())->searchable(),
                Forms\Components\Select::make('change_type')
                    ->label('Jenis')
                    ->options(['upgrade' => 'Upgrade', 'downgrade' => 'Downgrade'])
                    ->required(),
                Forms\Components\Textarea::make('notes')->label('Catatan')->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('change_date')->label('Tanggal')->date()->sortable(),
                Tables\Columns\TextColumn::make('service.cid')->label('CID')->searchable(),
                Tables\Columns\TextColumn::make('service.customer.name')->label('Pelanggan')->searchable(),
                Tables\Columns\TextColumn::make('oldProduct.name')->label('Profile Awal')->searchable(),
                Tables\Columns\TextColumn::make('newProduct.name')->label('Profile Baru')->searchable(),
                Tables\Columns\TextColumn::make('admin.name')->label('Admin')->searchable(),
                Tables\Columns\TextColumn::make('change_type')->label('Jenis')->badge(),
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
            'index' => Pages\ListServicePlanChanges::route('/'),
            'create' => Pages\CreateServicePlanChange::route('/create'),
            'edit' => Pages\EditServicePlanChange::route('/{record}/edit'),
        ];
    }
}
