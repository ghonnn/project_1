<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RouterInterfaceResource\Pages;
use App\Filament\Resources\RouterInterfaceResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\RouterInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RouterInterfaceResource extends Resource
{
    protected static ?string $model = RouterInterface::class;

    protected static ?string $navigationGroup = 'OSS';

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\Select::make('router_id')->options(fn () => AdminOptions::routers())->searchable()->required(),
                Forms\Components\TextInput::make('interface_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('interface_type')->maxLength(255),
                Forms\Components\TextInput::make('ip_address')->maxLength(255),
                Forms\Components\TextInput::make('vlan_id')->numeric(),
                Forms\Components\TextInput::make('speed_mbps')->numeric(),
                Forms\Components\Select::make('status')
                    ->options(['provisioning' => 'Provisioning', 'active' => 'Active', 'down' => 'Down'])
                    ->default('provisioning')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->searchable(),
                Tables\Columns\TextColumn::make('router.router_name')->label('Router')->searchable(),
                Tables\Columns\TextColumn::make('interface_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('ip_address')->searchable(),
                Tables\Columns\TextColumn::make('vlan_id')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
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
            'index' => Pages\ListRouterInterfaces::route('/'),
            'create' => Pages\CreateRouterInterface::route('/create'),
            'edit' => Pages\EditRouterInterface::route('/{record}/edit'),
        ];
    }
}
