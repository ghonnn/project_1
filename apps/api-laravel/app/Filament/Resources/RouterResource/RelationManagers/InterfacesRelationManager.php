<?php

namespace App\Filament\Resources\RouterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InterfacesRelationManager extends RelationManager
{
    protected static string $relationship = 'interfaces';

    protected static ?string $title = 'Interface Router';

    protected static ?string $modelLabel = 'Interface';

    protected static ?string $pluralModelLabel = 'Interface Router';

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Hidden::make('tenant_id')
                    ->default(fn () => $this->getOwnerRecord()->tenant_id),
                Forms\Components\TextInput::make('interface_name')
                    ->label('Nama Interface')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('interface_type')
                    ->label('Tipe')
                    ->options([
                        'ethernet' => 'Ethernet',
                        'vlan' => 'VLAN',
                        'bridge' => 'Bridge',
                        'pppoe' => 'PPPoE',
                        'hotspot' => 'Hotspot',
                        'wireless' => 'Wireless',
                    ])
                    ->searchable(),
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->maxLength(255),
                Forms\Components\TextInput::make('vlan_id')
                    ->label('VLAN ID')
                    ->numeric(),
                Forms\Components\TextInput::make('speed_mbps')
                    ->label('Speed Mbps')
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'provisioning' => 'Provisioning',
                        'active' => 'Aktif',
                        'down' => 'Down',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('interface_name')->label('Interface')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('interface_type')->label('Tipe')->badge(),
                Tables\Columns\TextColumn::make('ip_address')->label('IP Address')->searchable(),
                Tables\Columns\TextColumn::make('vlan_id')->label('VLAN')->sortable(),
                Tables\Columns\TextColumn::make('speed_mbps')->label('Mbps')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'down' => 'danger',
                        default => 'info',
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah Interface'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
