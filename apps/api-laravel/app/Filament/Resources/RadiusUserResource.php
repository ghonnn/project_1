<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadiusUserResource\Pages;
use App\Filament\Resources\RadiusUserResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\RadiusUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RadiusUserResource extends Resource
{
    protected static ?string $model = RadiusUser::class;

    protected static ?string $navigationGroup = 'Radius';

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna Radius';

    protected static ?string $pluralModelLabel = 'Pengguna Radius';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\Select::make('customer_id')->options(fn () => AdminOptions::customers())->searchable()->required(),
                Forms\Components\Select::make('service_id')->options(fn () => AdminOptions::services())->searchable()->required(),
                Forms\Components\Select::make('router_id')->options(fn () => AdminOptions::routers())->searchable(),
                Forms\Components\Select::make('profile_id')->label('Profil Langganan')->options(fn () => AdminOptions::radiusProfiles())->searchable(),
                Forms\Components\TextInput::make('username')->required()->maxLength(255),
                Forms\Components\TextInput::make('secret')->password()->revealable()->required()->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options(['pending' => 'Pending', 'active' => 'Active', 'suspended' => 'Suspended'])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->searchable(),
                Tables\Columns\TextColumn::make('username')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('service.cid')->label('Layanan')->searchable(),
                Tables\Columns\TextColumn::make('profile.name')->label('Profil Langganan')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'pending' => 'warning',
                    'suspended' => 'danger',
                    default => 'gray',
                }),
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
            'index' => Pages\ListRadiusUsers::route('/'),
            'create' => Pages\CreateRadiusUser::route('/create'),
            'edit' => Pages\EditRadiusUser::route('/{record}/edit'),
        ];
    }
}
