<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaidInvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Service;
use Carbon\CarbonInterface;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaidInvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Invoice paid';

    protected static ?string $modelLabel = 'Invoice paid';

    protected static ?string $pluralModelLabel = 'Invoice paid';

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?int $navigationSort = 11;

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Data Invoice Sudah Lunas')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('INVOICE')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('items.service.cid')->label('NO.LAYANAN')->default('-'),
                Tables\Columns\TextColumn::make('customer.name')->label('PELANGGAN')->searchable(),
                Tables\Columns\TextColumn::make('items.service.billing_profile_name')->label('PROFILE')->default('-'),
                Tables\Columns\TextColumn::make('items.service.partner_name')->label('PARTNER')->default('-'),
                Tables\Columns\TextColumn::make('items.description')->label('KATEGORI')->default('RECURRING')->limit(18),
                Tables\Columns\TextColumn::make('payments.paid_at')->label('TGL BAYAR')->dateTime('d/m/Y H:i:s'),
                Tables\Columns\TextColumn::make('admin')->label('ADMIN')->state('SYSTEM'),
                Tables\Columns\TextColumn::make('payments.method')->label('CARABAYAR')->default('-')->badge(),
                Tables\Columns\TextColumn::make('channel')->label('CHANNEL')->state(fn (Invoice $record): string => strtoupper((string) ($record->payments->first()?->method ?? '-'))),
                Tables\Columns\TextColumn::make('rekening')->label('REKENING')->state('-'),
                Tables\Columns\TextColumn::make('subtotal')->label('SUBTOTAL')->state(fn (Invoice $record): string => self::formatRupiah(self::subtotal($record))),
                Tables\Columns\TextColumn::make('diskon')->label('DISKON')->state('0'),
                Tables\Columns\TextColumn::make('ppn')->label('PPN')->state(fn (Invoice $record): string => self::formatRupiah(max(0, (float) $record->total_amount - self::subtotal($record)))),
                Tables\Columns\TextColumn::make('bhp')->label('BHP 0,5%')->state(fn (Invoice $record): string => self::formatRupiah(self::bhpAmount($record))),
                Tables\Columns\TextColumn::make('uso')->label('USO 1,25%')->state(fn (Invoice $record): string => self::formatRupiah(self::usoAmount($record))),
                Tables\Columns\TextColumn::make('adm')->label('ADM')->state('0'),
                Tables\Columns\TextColumn::make('kode')->label('KODE')->state('0'),
                Tables\Columns\TextColumn::make('total_amount')->label('TOTAL')->formatStateUsing(fn ($state) => self::formatRupiah($state))->sortable(),
                Tables\Columns\IconColumn::make('note')->label('NOTE')->boolean()->state(fn () => true)->trueIcon('heroicon-s-pencil-square')->trueColor('success'),
            ])
            ->filters([])
            ->actions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'items.service', 'payments'])
            ->where('status', 'paid');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaidInvoices::route('/'),
        ];
    }

    public static function formatRupiah(mixed $state): string
    {
        return number_format((float) $state, 0, ',', '.');
    }

    public static function formatCurrency(mixed $state): string
    {
        return 'Rp'.self::formatRupiah($state);
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

    public static function ppnAmount(Invoice $invoice): float
    {
        return max(0, (float) $invoice->total_amount - self::subtotal($invoice));
    }

    public static function bhpAmount(Invoice $invoice): float
    {
        return round(self::subtotal($invoice) * 0.005);
    }

    public static function usoAmount(Invoice $invoice): float
    {
        return round(self::subtotal($invoice) * 0.0125);
    }

    /**
     * @return array{invoice_count:int,gross:float,dpp:float,ppn:float,bhp:float,uso:float}
     */
    public static function financialSummary(?CarbonInterface $start = null, ?CarbonInterface $end = null): array
    {
        $invoices = self::paidInvoicesForPeriod($start, $end)->get();

        $dpp = $invoices->sum(fn (Invoice $invoice): float => self::subtotal($invoice));
        $gross = $invoices->sum(fn (Invoice $invoice): float => (float) $invoice->total_amount);

        return [
            'invoice_count' => $invoices->count(),
            'gross' => $gross,
            'dpp' => $dpp,
            'ppn' => max(0, $gross - $dpp),
            'bhp' => round($dpp * 0.005),
            'uso' => round($dpp * 0.0125),
        ];
    }

    /**
     * @return array{label:string,start:string,end:string,invoice_count:int,gross:float,dpp:float,ppn:float,bhp:float,uso:float}
     */
    public static function recapForPeriod(string $period): array
    {
        [$label, $start, $end] = match ($period) {
            'daily' => ['Rekap Harian', now()->startOfDay(), now()->endOfDay()],
            'weekly' => ['Rekap Mingguan', now()->startOfWeek(), now()->endOfWeek()],
            'monthly' => ['Rekap Bulanan', now()->startOfMonth(), now()->endOfMonth()],
            'yearly' => ['Rekap Tahunan', now()->startOfYear(), now()->endOfYear()],
            default => ['Rekap Harian', now()->startOfDay(), now()->endOfDay()],
        };

        return [
            'label' => $label,
            'start' => $start->format('d/m/Y H:i'),
            'end' => $end->format('d/m/Y H:i'),
            ...self::financialSummary($start, $end),
        ];
    }

    /**
     * @return Builder<Invoice>
     */
    private static function paidInvoicesForPeriod(?CarbonInterface $start = null, ?CarbonInterface $end = null): Builder
    {
        return Invoice::query()
            ->with(['items.service', 'payments'])
            ->where('status', 'paid')
            ->when($start && $end, fn (Builder $query) => $query->whereHas(
                'payments',
                fn (Builder $paymentQuery) => $paymentQuery->whereBetween('paid_at', [$start, $end])
            ));
    }
}
