<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UnpaidInvoiceOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $query = Invoice::query()->where('status', '!=', 'paid');

        return [
            Stat::make('Jumlah Invoice', number_format((clone $query)->count(), 0, ',', '.'))
                ->icon('heroicon-o-document-text')
                ->color('info'),
            Stat::make('Unpaid', number_format((clone $query)->whereIn('status', ['issued', 'overdue'])->count(), 0, ',', '.'))
                ->icon('heroicon-o-document-check')
                ->color('success'),
            Stat::make('Suspended', number_format((clone $query)->where('status', 'overdue')->count(), 0, ',', '.'))
                ->icon('heroicon-o-receipt-refund')
                ->color('danger'),
            Stat::make('Total blm dibayar', 'Rp'.number_format((clone $query)->sum('total_amount') - (clone $query)->sum('paid_amount'), 0, ',', '.'))
                ->icon('heroicon-o-document-currency-dollar')
                ->color('info'),
        ];
    }
}
