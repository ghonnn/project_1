<?php

namespace App\Filament\Resources\PaidInvoiceResource\Widgets;

use App\Filament\Resources\PaidInvoiceResource;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaidInvoiceOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = PaidInvoiceResource::financialSummary();
        $today = PaidInvoiceResource::financialSummary(now()->startOfDay(), now()->endOfDay());
        $week = PaidInvoiceResource::financialSummary(now()->startOfWeek(), now()->endOfWeek());
        $month = PaidInvoiceResource::financialSummary(now()->startOfMonth(), now()->endOfMonth());
        $year = PaidInvoiceResource::financialSummary(now()->startOfYear(), now()->endOfYear());

        return [
            Stat::make('Pendapatan DPP', PaidInvoiceResource::formatCurrency($total['dpp']))
                ->description('Total pendapatan di luar PPN')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Total PPN', PaidInvoiceResource::formatCurrency($total['ppn']))
                ->description('Selisih total final dikurangi DPP')
                ->icon('heroicon-o-percent-badge')
                ->color('warning'),
            Stat::make('BHP Telekomunikasi 0,5%', PaidInvoiceResource::formatCurrency($total['bhp']))
                ->description('0,5% dari DPP sebelum PPN')
                ->icon('heroicon-o-scale')
                ->color('info'),
            Stat::make('Kontribusi USO 1,25%', PaidInvoiceResource::formatCurrency($total['uso']))
                ->description('1,25% dari DPP sebelum PPN')
                ->icon('heroicon-o-receipt-percent')
                ->color('danger'),
            Stat::make('Harian', PaidInvoiceResource::formatCurrency($today['dpp']))
                ->description($today['invoice_count'].' invoice lunas')
                ->icon('heroicon-o-calendar-days')
                ->color('gray'),
            Stat::make('Mingguan', PaidInvoiceResource::formatCurrency($week['dpp']))
                ->description($week['invoice_count'].' invoice lunas')
                ->icon('heroicon-o-calendar-date-range')
                ->color('gray'),
            Stat::make('Bulanan '.Carbon::now()->translatedFormat('F Y'), PaidInvoiceResource::formatCurrency($month['dpp']))
                ->description($month['invoice_count'].' invoice lunas')
                ->icon('heroicon-o-calendar')
                ->color('gray'),
            Stat::make('Tahunan '.Carbon::now()->year, PaidInvoiceResource::formatCurrency($year['dpp']))
                ->description($year['invoice_count'].' invoice lunas')
                ->icon('heroicon-o-chart-bar')
                ->color('gray'),
        ];
    }
}
