<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Pembayaran';

    protected static ?string $modelLabel = 'Pembayaran';

    protected static ?string $pluralModelLabel = 'Pembayaran';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('tenant_id')->options(fn () => AdminOptions::tenants())->searchable()->required(),
                Forms\Components\Select::make('invoice_id')->options(fn () => AdminOptions::invoices())->searchable()->required(),
                Forms\Components\TextInput::make('amount')->numeric()->prefix('Rp')->required(),
                Forms\Components\Select::make('method')
                    ->options(['manual' => 'Manual', 'transfer' => 'Transfer', 'cash' => 'Cash', 'gateway' => 'Gateway'])
                    ->default('manual')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(['initiated' => 'Initiated', 'paid' => 'Paid', 'failed' => 'Failed', 'refunded' => 'Refunded'])
                    ->default('initiated')
                    ->required(),
                Forms\Components\TextInput::make('external_ref')->maxLength(255),
                Forms\Components\DateTimePicker::make('paid_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->searchable(),
                Tables\Columns\TextColumn::make('invoice.invoice_number')->label('Invoice')->searchable(),
                Tables\Columns\TextColumn::make('amount')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('method')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'paid' => 'success',
                    'initiated' => 'info',
                    'refunded' => 'warning',
                    'failed' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->sortable(),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
