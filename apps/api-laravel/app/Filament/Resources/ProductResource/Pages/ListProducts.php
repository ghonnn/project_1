<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Profil Langganan';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('set_active_hint')
                    ->label('Set Aktif')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn () => Notification::make()->title('Centang profile pada tabel lalu gunakan bulk action Set Aktif.')->info()->send()),
                Actions\Action::make('set_inactive_hint')
                    ->label('Non Aktif')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->action(fn () => Notification::make()->title('Centang profile pada tabel lalu gunakan bulk action Non Aktif.')->warning()->send()),
                Actions\Action::make('delete_hint')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(fn () => Notification::make()->title('Centang profile pada tabel lalu gunakan bulk action Delete.')->danger()->send()),
            ])
                ->label('Menu')
                ->icon('heroicon-o-bars-3')
                ->color('info')
                ->button(),
            Actions\CreateAction::make()
                ->label('Tambah')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->modalHeading('Profile Berlangganan'),
        ];
    }
}
