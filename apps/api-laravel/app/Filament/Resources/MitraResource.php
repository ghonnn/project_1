<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MitraResource\Pages;
use App\Filament\Support\AdminOptions;
use App\Models\Mitra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MitraResource extends Resource
{
    protected static ?string $model = Mitra::class;

    protected static ?string $navigationGroup = 'Mitra';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Data Mitra';

    protected static ?string $modelLabel = 'Mitra';

    protected static ?string $pluralModelLabel = 'Mitra';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\TextInput::make('code')->label('Kode mitra')->placeholder('Otomatis jika dikosongkan')->maxLength(255),
                Forms\Components\TextInput::make('name')->label('Nama mitra')->required()->maxLength(255),
                Forms\Components\TextInput::make('outlet_name')->label('Nama outlet')->maxLength(255),
                Forms\Components\TextInput::make('phone')->label('No. HP')->tel()->maxLength(255),
                Forms\Components\Textarea::make('address')->label('Alamat')->rows(2),
                Forms\Components\Select::make('commission_type')
                    ->label('Jenis komisi')
                    ->options(['nominal' => 'Nominal (Rp)', 'percentage' => 'Persentase (%)'])
                    ->default('nominal')
                    ->required(),
                Forms\Components\TextInput::make('commission_value')->label('Nilai komisi')->numeric()->default(0)->required(),
                Forms\Components\TextInput::make('balance')->label('Saldo')->numeric()->prefix('Rp')->default(0),
                Forms\Components\Select::make('status')
                    ->options(['active' => 'Aktif', 'inactive' => 'Non Aktif'])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama Mitra')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('outlet_name')->label('Outlet')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('No. HP')->searchable(),
                Tables\Columns\TextColumn::make('commission_type')->label('Jenis Komisi')->badge()->color('info'),
                Tables\Columns\TextColumn::make('commission_value')->label('Nilai Komisi')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('balance')->label('Saldo')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    default => 'gray',
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Aktif', 'inactive' => 'Non Aktif']),
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
            'index' => Pages\ListMitras::route('/'),
            'create' => Pages\CreateMitra::route('/create'),
            'edit' => Pages\EditMitra::route('/{record}/edit'),
        ];
    }
}
