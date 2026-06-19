<?php

namespace App\Filament\Resources\StoppedSubscriptionResource\Pages;

use App\Filament\Resources\StoppedSubscriptionResource;
use App\Filament\Support\AdminOptions;
use App\Models\Service;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListStoppedSubscriptions extends ListRecords
{
    protected static string $resource = StoppedSubscriptionResource::class;

    protected static ?string $title = 'Berhenti Langganan';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('stop_subscription')
                ->label('Tambah')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->modalHeading('Stop Berlangganan')
                ->form([
                    Forms\Components\Select::make('service_id')
                        ->label('No. Layanan')
                        ->options(fn () => AdminOptions::services())
                        ->searchable()
                        ->required(),
                    Forms\Components\DatePicker::make('terminated_at')
                        ->label('Tanggal berhenti')
                        ->default(now())
                        ->required(),
                    Forms\Components\Textarea::make('reason')
                        ->label('Alasan berhenti')
                        ->rows(4),
                    Forms\Components\TextInput::make('estimated_bill')
                        ->label('Perkiraan tagihan harus dibayar')
                        ->prefix('Rp')
                        ->disabled(),
                    Forms\Components\Placeholder::make('notice')
                        ->content('Modem pelanggan harus dikembalikan jika status dipinjamkan. Modem harus di-reset dan konfigurasi dikosongkan. Data berhenti tidak bisa diaktifkan ulang dengan no. layanan ini.'),
                ])
                ->action(function (array $data): void {
                    $service = Service::query()->findOrFail($data['service_id']);
                    $service->update([
                        'status' => 'terminated',
                        'terminated_at' => $data['terminated_at'],
                        'notes' => trim(($service->notes ? $service->notes."\n" : '').($data['reason'] ?? '')),
                    ]);

                    Notification::make()->title('Layanan berhasil dihentikan')->success()->send();
                }),
        ];
    }
}
