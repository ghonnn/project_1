<?php

namespace App\Filament\Resources\PaidInvoiceResource\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaidInvoiceOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $query = Invoice::query()->where('status', 'paid');
        $monthTotal = (clone $query)
            ->whereMonth('issue_date', Carbon::now()->month)
            ->whereYear('issue_date', Carbon::now()->year)
            ->sum('total_amount');

        return [
            Stat::make('Total Komisi', 'Rp0')->icon('heroicon-o-circle-stack')->color('info'),
            Stat::make('Biaya Admin', 'Rp0')->icon('heroicon-o-banknotes')->color('success'),
            Stat::make('Total PPN', 'Rp'.number_format(round((clone $query)->sum('total_amount') * 11 / 111), 0, ',', '.'))->icon('heroicon-o-percent-badge')->color('warning'),
            Stat::make('Total '.Carbon::now()->translatedFormat('F Y'), 'Rp'.number_format($monthTotal, 0, ',', '.'))->icon('heroicon-o-calendar-days')->color('info'),
        ];
    }
}
