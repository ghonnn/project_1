<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Services\ServiceProvisioningService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    /** @var array<string, mixed> */
    private array $provisionData = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        [$data, $this->provisionData] = $this->extractProvisionData($data);

        return $data;
    }

    protected function afterSave(): void
    {
        if (! ($this->provisionData['router_id'] ?? null)) {
            return;
        }

        $result = app(ServiceProvisioningService::class)->provision($this->record, $this->provisionData);

        Notification::make()
            ->title('Koneksi router layanan diperbarui')
            ->body('Router, Radius user, dan layanan aktif'.($result['invoice'] ? ', invoice awal dibuat.' : '.'))
            ->success()
            ->send();
    }

    /**
     * @param array<string, mixed> $data
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function extractProvisionData(array $data): array
    {
        $provision = [
            'router_id' => $data['provision_router_id'] ?? null,
            'interface_id' => $data['provision_interface_id'] ?? null,
            'vlan_id' => $data['provision_vlan_id'] ?? null,
            'username' => $data['internet_username'] ?? null,
            'password' => $data['internet_password'] ?? null,
            'create_invoice' => (bool) ($data['provision_create_invoice'] ?? false),
        ];

        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'provision_')) {
                unset($data[$key]);
            }
        }

        return [$data, $provision];
    }
}
