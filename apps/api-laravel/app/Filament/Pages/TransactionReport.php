<?php

namespace App\Filament\Pages;

use App\Filament\Support\FinanceTablePage;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class TransactionReport extends FinanceTablePage
{
    protected static ?string $navigationLabel = 'Transaksi';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?int $navigationSort = 80;

    public function stats(): array
    {
        $paid = Payment::query()->where('status', 'paid');
        $income = (clone $paid)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        return [
            ['label' => 'Pemasukan '.now()->translatedFormat('F'), 'value' => self::rupiah($income), 'icon' => 'heroicon-o-calendar-days', 'color' => 'info'],
            ['label' => 'Pengeluaran '.now()->translatedFormat('F'), 'value' => '0', 'icon' => 'heroicon-o-calendar', 'color' => 'warning'],
            ['label' => 'Laba '.now()->translatedFormat('F'), 'value' => self::rupiah($income), 'icon' => 'heroicon-o-calendar-date-range', 'color' => 'success'],
        ];
    }

    public function tableTitle(): string
    {
        return 'Data Transaksi';
    }

    public function toolbarActions(): array
    {
        return [
            ['label' => 'Tambah', 'color' => 'success'],
            ['label' => 'Harian', 'color' => 'danger'],
            ['label' => 'Bulanan', 'color' => 'info'],
            ['label' => 'Export', 'color' => 'success'],
            ['label' => 'Kosongkan', 'color' => 'info'],
        ];
    }

    public function columns(): array
    {
        return ['#', 'TANGGAL', 'KATEGORI', 'JENIS', 'ADMIN', 'DESKRIPSI', 'QTY', 'TOTAL'];
    }

    public function rows(): array
    {
        return Payment::query()
            ->with('invoice')
            ->where('status', 'paid')
            ->latest('paid_at')
            ->limit(25)
            ->get()
            ->values()
            ->map(fn (Payment $payment, int $index): array => [
                (string) ($index + 1),
                ($payment->paid_at ?: Carbon::parse($payment->created_at))->format('d/m/Y'),
                'PEMASUKAN',
                'INVOICE',
                'SYSTEM',
                'Pembayaran invoice '.$payment->invoice?->invoice_number,
                '1',
                self::rupiah($payment->amount),
            ])
            ->all();
    }

    private static function rupiah(mixed $amount): string
    {
        return number_format((float) $amount, 0, ',', '.');
    }
}
