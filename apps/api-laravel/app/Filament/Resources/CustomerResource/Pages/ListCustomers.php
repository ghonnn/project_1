<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected static ?string $title = 'Data Pelanggan';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('set_active_hint')
                    ->label('Set Aktif')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn () => Notification::make()->title('Centang pelanggan pada tabel lalu gunakan bulk action Set Aktif.')->info()->send()),
                Actions\Action::make('set_inactive_hint')
                    ->label('Non Aktif')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->action(fn () => Notification::make()->title('Centang pelanggan pada tabel lalu gunakan bulk action Non Aktif.')->warning()->send()),
                Actions\Action::make('delete_hint')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(fn () => Notification::make()->title('Centang pelanggan pada tabel lalu gunakan bulk action Delete.')->danger()->send()),
            ])
                ->label('Menu')
                ->icon('heroicon-o-bars-3')
                ->color('info')
                ->button(),
            Actions\Action::make('import')
                ->label('Import')
                ->icon('heroicon-o-document-arrow-up')
                ->color('gray')
                ->modalHeading('Import')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Pilih file (EXCEL, CSV)')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
                ])
                ->modalSubmitActionLabel('Import')
                ->extraModalFooterActions([
                    Actions\Action::make('download_format')
                        ->label('Download Format')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('danger')
                        ->action(fn () => Notification::make()->title('Format import akan dibuat pada step berikutnya.')->info()->send()),
                ])
                ->action(fn () => Notification::make()->title('Import pelanggan akan diproses pada step berikutnya.')->success()->send()),
            Actions\Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(fn () => Notification::make()->title('Export pelanggan akan dibuat pada step berikutnya.')->info()->send()),
            Actions\CreateAction::make()
                ->label('Tambah')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->modalHeading('Pelanggan'),
        ];
    }
}
