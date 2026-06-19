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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RouterResource extends Resource
{
    protected static ?string $model = Router::class;

    protected static ?string $navigationGroup = 'Network';

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\TextInput::make('router_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('hostname')->required()->maxLength(255),
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
                Forms\Components\TextInput::make('management_ip')->required()->maxLength(255),
                Forms\Components\TextInput::make('public_ip')->maxLength(255),
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
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->searchable(),
                Tables\Columns\TextColumn::make('router_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('hostname')->searchable(),
                Tables\Columns\TextColumn::make('management_ip')->searchable(),
                Tables\Columns\TextColumn::make('public_ip')->searchable(),
                Tables\Columns\TextColumn::make('router_role')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
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
