<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadiusProfileResource\Pages;
use App\Filament\Resources\RadiusProfileResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\RadiusProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RadiusProfileResource extends Resource
{
    protected static ?string $model = RadiusProfile::class;

    protected static ?string $navigationGroup = 'Radius';

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Profil';

    protected static ?string $modelLabel = 'Profil Radius';

    protected static ?string $pluralModelLabel = 'Profil Radius';

    protected static ?int $navigationSort = 20;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\KeyValue::make('attributes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
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
            'index' => Pages\ListRadiusProfiles::route('/'),
            'create' => Pages\CreateRadiusProfile::route('/create'),
            'edit' => Pages\EditRadiusProfile::route('/{record}/edit'),
        ];
    }
}
