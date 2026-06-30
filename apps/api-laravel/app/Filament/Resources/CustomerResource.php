<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationGroup = 'Pelanggan';

    protected static ?string $navigationLabel = 'Pelanggan';

    protected static ?string $modelLabel = 'Data Pelanggan';

    protected static ?string $pluralModelLabel = 'Data Pelanggan';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('customer_number')
                    ->label('ID')
                    ->helperText('Isi dengan angka, jika kosong akan dibuatkan otomatis')
                    ->maxLength(20),
                Forms\Components\Select::make('tenant_id')->label('Tenant')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(120),
                Forms\Components\TextInput::make('phone')->label('Phone')->tel()->required()->maxLength(20),
                Forms\Components\Textarea::make('address')->label('Alamat')->required()->rows(3)->maxLength(500)->columnSpanFull(),
                Forms\Components\TextInput::make('identity_number')->label('No. Identitas')->maxLength(32),
                Forms\Components\TextInput::make('tax_number')->label('No. NPWP')->maxLength(32),
                Forms\Components\TextInput::make('partner_name')->label('Partner')->maxLength(80),
                Forms\Components\TextInput::make('balance')->label('Saldo')->numeric()->prefix('Rp')->default(0),
                Forms\Components\TextInput::make('client_area_url')->label('URL Client Area')->url()->maxLength(2048),
                Forms\Components\Hidden::make('type')->default('individual'),
                Forms\Components\Hidden::make('status')->default('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Data Pelanggan')
            ->columns([
                Tables\Columns\TextColumn::make('customer_number')->label('User ID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Pelanggan')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label('Phone')->searchable(),
                Tables\Columns\TextColumn::make('address')->label('Alamat')->searchable()->limit(45),
                Tables\Columns\TextColumn::make('balance')->label('Saldo')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('partner_name')->label('Partner')->searchable(),
                Tables\Columns\TextColumn::make('client_area_url')->label('URL Client Area')->searchable()->limit(48)->copyable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'suspended' => 'warning',
                    'terminated' => 'danger',
                    default => 'gray',
                })->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Aktif', 'suspended' => 'Non Aktif', 'terminated' => 'Berhenti'])
                    ->default('active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('set_active')
                        ->label('Set Aktif')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'active']))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('set_inactive')
                        ->label('Non Aktif')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => 'suspended']))
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
