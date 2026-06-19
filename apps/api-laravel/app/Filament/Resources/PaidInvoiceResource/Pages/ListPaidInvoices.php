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
            Actions\Action::make('daily')->label('Rekap Harian')->icon('heroicon-o-calendar-days')->color('info'),
            Actions\Action::make('monthly')->label('Rekap Bulanan')->icon('heroicon-o-calendar')->color('success'),
        ];
    }
}
