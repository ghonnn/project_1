<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationGroup = 'Tagihan';

    protected static ?string $navigationLabel = 'Invoice';

    protected static ?string $modelLabel = 'Invoice';

    protected static ?string $pluralModelLabel = 'Invoice';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->options(fn () => AdminOptions::tenants())
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('customer_id', null)),
                Forms\Components\Select::make('customer_id')
                    ->label('Pelanggan')
                    ->options(fn (Forms\Get $get) => AdminOptions::customers($get('tenant_id')))
                    ->getSearchResultsUsing(fn (string $search, Forms\Get $get): array => AdminOptions::customers($get('tenant_id'), $search))
                    ->getOptionLabelUsing(fn (?string $value): ?string => AdminOptions::customerOptionLabel($value))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('invoice_number')->required()->maxLength(255),
                Forms\Components\DatePicker::make('issue_date')->required(),
                Forms\Components\DatePicker::make('due_date')->required(),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'issued' => 'Issued', 'paid' => 'Paid', 'overdue' => 'Overdue', 'void' => 'Void'])
                    ->default('issued')
                    ->required(),
                Forms\Components\TextInput::make('total_amount')->numeric()->prefix('Rp')->required(),
                Forms\Components\TextInput::make('paid_amount')->numeric()->prefix('Rp')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Tenant')->searchable(),
                Tables\Columns\TextColumn::make('invoice_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('issue_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'paid' => 'success',
                    'issued' => 'info',
                    'overdue' => 'danger',
                    default => 'gray',
                }),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
