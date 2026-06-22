<?php

namespace App\Filament\Resources\RouterResource\Pages;

use App\Filament\Resources\RouterResource;
use App\Services\RouterProvisioningService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRouter extends EditRecord
{
    protected static string $resource = RouterResource::class;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return RouterResource::normalizeRouterSettings($data, $this->record);
    }

    protected function afterSave(): void
    {
        $provisioning = app(RouterProvisioningService::class);
        $server = $provisioning->radiusServerForRouter($this->record);

        if ($server !== null) {
            $provisioning->ensureNasDevice($this->record, $server);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
