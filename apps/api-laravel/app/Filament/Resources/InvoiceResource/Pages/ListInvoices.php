<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\InvoiceResource\Widgets\UnpaidInvoiceOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            UnpaidInvoiceOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pay_hint')->label('Bayar')->icon('heroicon-o-banknotes')->color('success'),
            Actions\Action::make('print')->label('Print')->icon('heroicon-o-printer')->color('gray'),
            Actions\Action::make('reminder')->label('Rapel')->icon('heroicon-o-chat-bubble-left-right')->color('info'),
            Actions\Action::make('debt')->label('Utang')->icon('heroicon-o-circle-stack')->color('warning'),
            Actions\Action::make('export')->label('Export')->icon('heroicon-o-arrow-up-tray')->color('info'),
            Actions\CreateAction::make()->label('INV Baru')->icon('heroicon-o-plus-circle')->color('primary'),
        ];
    }
}
