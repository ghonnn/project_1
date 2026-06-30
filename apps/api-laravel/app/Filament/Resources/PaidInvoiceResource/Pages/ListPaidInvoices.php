<?php

namespace App\Filament\Resources\PaidInvoiceResource\Pages;

use App\Filament\Resources\PaidInvoiceResource;
use App\Filament\Resources\PaidInvoiceResource\Widgets\PaidInvoiceOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaidInvoices extends ListRecords
{
    protected static string $resource = PaidInvoiceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PaidInvoiceOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')->label('Print')->icon('heroicon-o-printer')->color('gray'),
            Actions\Action::make('notify')->label('Notif WA')->icon('heroicon-o-chat-bubble-left-right')->color('success'),
            Actions\Action::make('export')->label('Export')->icon('heroicon-o-arrow-up-tray')->color('info'),
            $this->recapAction('daily', 'Rekap Harian', 'heroicon-o-calendar-days', 'info'),
            $this->recapAction('weekly', 'Rekap Mingguan', 'heroicon-o-calendar-date-range', 'warning'),
            $this->recapAction('monthly', 'Rekap Bulanan', 'heroicon-o-calendar', 'success'),
            $this->recapAction('yearly', 'Rekap Tahunan', 'heroicon-o-chart-bar', 'gray'),
        ];
    }

    private function recapAction(string $period, string $label, string $icon, string $color): Actions\Action
    {
        return Actions\Action::make($period)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->modalHeading($label)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalContent(fn () => view('filament.resources.paid-invoice-resource.recap-modal', [
                'recap' => PaidInvoiceResource::recapForPeriod($period),
            ]));
    }
}
