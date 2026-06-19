<?php

namespace App\Filament\Resources\ServiceResource\RelationManagers;

use App\Filament\Support\AdminOptions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PlanChangesRelationManager extends RelationManager
{
    protected static string $relationship = 'planChanges';

    protected static ?string $title = 'Naik / Turun Paket';

    protected static ?string $modelLabel = 'Perubahan Paket';

    protected static ?string $pluralModelLabel = 'Naik / Turun Paket';

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Hidden::make('tenant_id')
                    ->default(fn () => $this->getOwnerRecord()->tenant_id),
                Forms\Components\DatePicker::make('change_date')
                    ->label('Tanggal')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('old_product_id')
                    ->label('Profile Awal')
                    ->options(fn () => AdminOptions::products($this->getOwnerRecord()->tenant_id))
                    ->default(fn () => $this->getOwnerRecord()->product_id)
                    ->disabled()
                    ->dehydrated()
                    ->searchable(),
                Forms\Components\Select::make('new_product_id')
                    ->label('Profile Baru')
                    ->options(fn () => AdminOptions::products($this->getOwnerRecord()->tenant_id))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('admin_user_id')
                    ->label('Admin')
                    ->options(fn () => AdminOptions::users())
                    ->default(fn () => Auth::id())
                    ->searchable(),
                Forms\Components\Select::make('change_type')
                    ->label('Jenis')
                    ->options(['upgrade' => 'Upgrade', 'downgrade' => 'Downgrade'])
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('change_date')->label('Tanggal')->date()->sortable(),
                Tables\Columns\TextColumn::make('oldProduct.name')->label('Profile Awal')->searchable(),
                Tables\Columns\TextColumn::make('newProduct.name')->label('Profile Baru')->searchable(),
                Tables\Columns\TextColumn::make('admin.name')->label('Admin')->searchable(),
                Tables\Columns\TextColumn::make('change_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'upgrade' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah Perubahan Paket'),
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
