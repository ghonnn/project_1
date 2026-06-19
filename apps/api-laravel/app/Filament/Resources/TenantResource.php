<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationGroup = 'Platform';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Tenant';

    protected static ?string $modelLabel = 'Tenant';

    protected static ?string $pluralModelLabel = 'Tenant';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->required()->maxLength(255),
                Forms\Components\Section::make('Lisensi Platform')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('plan')
                            ->label('Paket Lisensi')
                            ->options(['NEX BASIC' => 'NEX BASIC'])
                            ->default('NEX BASIC')
                            ->required(),
                        Forms\Components\TextInput::make('license_max_sessions')
                            ->label('Maks. Sesi Online')
                            ->numeric()
                            ->default(250)
                            ->required(),
                        Forms\Components\TextInput::make('license_max_vouchers')
                            ->label('Maks. Voucher')
                            ->numeric()
                            ->default(5000)
                            ->required(),
                        Forms\Components\TextInput::make('license_max_subscriptions')
                            ->label('Maks. Berlangganan')
                            ->numeric()
                            ->default(200)
                            ->required(),
                        Forms\Components\TextInput::make('license_max_routers')
                            ->label('Maks. Router')
                            ->numeric()
                            ->default(2)
                            ->required(),
                    ]),
                Forms\Components\FileUpload::make('logo_path')
                    ->label('Logo Admin Panel')
                    ->disk('public')
                    ->directory('tenant-logos')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('200')
                    ->imageResizeTargetHeight('200')
                    ->helperText('Upload logo ukuran 200px x 200px untuk pojok kiri atas admin panel.'),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'suspended' => 'Suspended'])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\ImageColumn::make('logo_path')->label('Logo')->disk('public')->square(),
                Tables\Columns\TextColumn::make('plan')->label('Paket Lisensi')->badge(),
                Tables\Columns\TextColumn::make('license_max_sessions')->label('Sesi'),
                Tables\Columns\TextColumn::make('license_max_subscriptions')->label('Langganan'),
                Tables\Columns\TextColumn::make('license_max_routers')->label('Router'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'suspended' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
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
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
