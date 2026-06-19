<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NasDeviceResource\Pages;
use App\Filament\Support\AdminOptions;
use App\Models\NasDevice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NasDeviceResource extends Resource
{
    protected static ?string $model = NasDevice::class;

    protected static ?string $navigationGroup = 'Radius';

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'NAS Device';

    protected static ?string $modelLabel = 'NAS Device';

    protected static ?string $pluralModelLabel = 'NAS Devices';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->label('Tenant')
                    ->options(fn () => AdminOptions::tenants())
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set): void {
                        $set('router_id', null);
                        $set('radius_server_id', null);
                    }),
                Forms\Components\Select::make('router_id')
                    ->label('Router')
                    ->options(fn (Forms\Get $get) => AdminOptions::routers($get('tenant_id')))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('radius_server_id')
                    ->label('Radius Server')
                    ->options(fn (Forms\Get $get) => AdminOptions::radiusServers($get('tenant_id')))
                    ->searchable(),
                Forms\Components\TextInput::make('hostname')
                    ->label('NAS Name / Hostname')
                    ->required()
                    ->maxLength(80),
                Forms\Components\TextInput::make('nas_ip_address')
                    ->label('NAS IP Address')
                    ->required()
                    ->maxLength(45),
                Forms\Components\TextInput::make('vendor_type')
                    ->label('Vendor Type')
                    ->placeholder('MikroTik / Cisco / Juniper')
                    ->maxLength(50),
                Forms\Components\TextInput::make('secret')
                    ->label('NAS Secret')
                    ->password()
                    ->revealable()
                    ->required()
                    ->maxLength(80),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Maintenance'])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->searchable(),
                Tables\Columns\TextColumn::make('hostname')->label('NAS')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nas_ip_address')->label('IP')->searchable(),
                Tables\Columns\TextColumn::make('router.router_name')->label('Router')->searchable(),
                Tables\Columns\TextColumn::make('radiusServer.name')->label('Radius Server')->toggleable(),
                Tables\Columns\TextColumn::make('vendor_type')->label('Vendor')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'maintenance' => 'warning',
                    default => 'gray',
                }),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNasDevices::route('/'),
            'create' => Pages\CreateNasDevice::route('/create'),
            'edit' => Pages\EditNasDevice::route('/{record}/edit'),
        ];
    }
}
