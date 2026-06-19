<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceRouterMappingResource\Pages;
use App\Filament\Support\AdminOptions;
use App\Models\RouterInterface;
use App\Models\Service;
use App\Models\ServiceRouterMapping;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class ServiceRouterMappingResource extends Resource
{
    protected static ?string $model = ServiceRouterMapping::class;

    protected static ?string $navigationGroup = 'OSS';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Service Router Mappings';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Mapping')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->options(fn () => AdminOptions::tenants())
                            ->searchable()
                            ->required()
                            ->live(),
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
                        Forms\Components\Select::make('router_id')
                            ->options(fn () => AdminOptions::routers())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('interface_id', null)),
                        Forms\Components\Select::make('interface_id')
                            ->label('Router interface')
                            ->options(fn (Forms\Get $get) => AdminOptions::routerInterfaces($get('router_id')))
                            ->searchable()
                            ->nullable()
                            ->rules([
                                fn (Forms\Get $get) => Rule::exists('router_interfaces', 'id')->where('router_id', $get('router_id')),
                            ])
                            ->disabled(fn (Forms\Get $get): bool => blank($get('router_id'))),
                        Forms\Components\TextInput::make('vlan_id')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(4094),
                        Forms\Components\Toggle::make('is_primary')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('service.customer.name')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('service.cid')->label('Service CID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('router.router_name')->label('Router')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('interface.interface_name')->label('Interface')->searchable(),
                Tables\Columns\TextColumn::make('vlan_id')->label('VLAN')->sortable(),
                Tables\Columns\IconColumn::make('is_primary')->boolean()->label('Primary'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tenant_id')->label('Tenant')->options(fn () => AdminOptions::tenants()),
                Tables\Filters\SelectFilter::make('router_id')->label('Router')->options(fn () => AdminOptions::routers()),
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
            'index' => Pages\ListServiceRouterMappings::route('/'),
            'create' => Pages\CreateServiceRouterMapping::route('/create'),
            'edit' => Pages\EditServiceRouterMapping::route('/{record}/edit'),
        ];
    }
}
