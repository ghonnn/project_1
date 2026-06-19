<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Filament\Support\AdminOptions;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Invoice unpaid';

    protected static ?string $modelLabel = 'Invoice unpaid';

    protected static ?string $pluralModelLabel = 'Invoice unpaid';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
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
                Forms\Components\TextInput::make('invoice_number')->required()->maxLength(50),
                Forms\Components\DatePicker::make('issue_date')->required(),
                Forms\Components\DatePicker::make('due_date')->required(),
                Forms\Components\Select::make('status')
                    ->options(['draft' => 'Draft', 'issued' => 'Issued', 'paid' => 'Paid', 'overdue' => 'Overdue', 'void' => 'Void'])
                    ->default('issued')
                    ->required(),
                Forms\Components\TextInput::make('total_amount')->numeric()->prefix('Rp')->required()->maxLength(14),
                Forms\Components\TextInput::make('paid_amount')->numeric()->prefix('Rp')->required()->maxLength(14),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Data Invoice Belum Lunas')
            ->columns([
                Tables\Columns\TextColumn::make('status')->label('STATUS')->badge()->color(fn (string $state): string => match ($state) {
                    'paid' => 'success',
                    'overdue' => 'danger',
                    'issued' => 'info',
                    default => 'warning',
                }),
                Tables\Columns\TextColumn::make('invoice_number')->label('INVOICE')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('items.service.cid')->label('NO. LAYANAN')->default('-')->searchable(),
                Tables\Columns\TextColumn::make('customer.name')->label('PELANGGAN')->searchable(),
                Tables\Columns\TextColumn::make('items.service.billing_profile_name')->label('PROFILE')->default('-'),
                Tables\Columns\TextColumn::make('items.service.partner_name')->label('MITRA')->default('-'),
                Tables\Columns\TextColumn::make('items.description')->label('KATEGORI')->default('RECURRING')->limit(18),
                Tables\Columns\TextColumn::make('issue_date')->label('TGL TERBIT')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('due_date')->label('JTH TEMPO')->date('d/m/Y')->sortable()->color('danger'),
                Tables\Columns\TextColumn::make('subtotal')->label('SUBTOTAL')->state(fn (Invoice $record): string => self::formatRupiah(self::subtotal($record))),
                Tables\Columns\TextColumn::make('discount')->label('DISKON')->state('0'),
                Tables\Columns\TextColumn::make('ppn')->label('PPN')->state(fn (Invoice $record): string => self::formatRupiah(max(0, (float) $record->total_amount - self::subtotal($record)))),
                Tables\Columns\TextColumn::make('code')->label('KODE')->state('0'),
                Tables\Columns\TextColumn::make('total_amount')->label('TOTAL')->formatStateUsing(fn ($state) => self::formatRupiah($state))->sortable(),
                Tables\Columns\IconColumn::make('note')->label('NOTE')->boolean()->state(fn () => true)->trueIcon('heroicon-s-pencil-square')->trueColor('success'),
                Tables\Columns\TextColumn::make('tagih')->label('TAGIH')->state('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Semua INV')
                    ->options(['issued' => 'Issued', 'overdue' => 'Overdue', 'draft' => 'Draft']),
            ])
            ->actions([
                Tables\Actions\Action::make('pay')
                    ->label('Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->modalHeading('Pilih Cara Bayar')
                    ->form([
                        Forms\Components\Select::make('method')
                            ->label('Cara Bayar')
                            ->options([
                                'cash' => 'Bayar Tunai',
                                'bank_transfer' => 'Transfer Bank',
                            ])
                            ->default('cash')
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Nominal Bayar')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->maxLength(14)
                            ->default(fn (Invoice $record): string => (string) max(0, (float) $record->total_amount - (float) $record->paid_amount)),
                    ])
                    ->action(function (Invoice $record, array $data): void {
                        Payment::query()->create([
                            'tenant_id' => $record->tenant_id,
                            'invoice_id' => $record->id,
                            'amount' => $data['amount'],
                            'method' => $data['method'],
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);

                        $paidAmount = (float) $record->paid_amount + (float) $data['amount'];
                        $record->update([
                            'paid_amount' => $paidAmount,
                            'status' => $paidAmount >= (float) $record->total_amount ? 'paid' : $record->status,
                        ]);

                        Notification::make()->title('Pembayaran invoice berhasil disimpan')->success()->send();
                    }),
                Tables\Actions\Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->modalHeading('Pilih Jenis Kertas')
                    ->form([
                        Forms\Components\Select::make('paper')
                            ->label('Jenis Kertas')
                            ->options([
                                'roll' => 'Roll Paper',
                                'a4' => 'A4 Template',
                            ])
                            ->default('roll')
                            ->required(),
                    ])
                    ->action(fn (Invoice $record, array $data) => redirect()->to(route('invoice.print', ['invoice' => $record, 'paper' => $data['paper']]))),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'items.service'])
            ->where('status', '!=', 'paid');
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

    public static function formatRupiah(mixed $state): string
    {
        return number_format((float) $state, 0, ',', '.');
    }

    public static function subtotal(Invoice $invoice): float
    {
        $service = $invoice->items->first()?->service;

        if ($service instanceof Service && (bool) $service->ppn_enabled) {
            $rate = (float) ($service->ppn_rate ?: 11);

            return round(((float) $invoice->total_amount) / (1 + ($rate / 100)));
        }

        return (float) $invoice->total_amount;
    }
}
