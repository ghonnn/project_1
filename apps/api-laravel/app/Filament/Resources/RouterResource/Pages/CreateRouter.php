<?php

namespace App\Filament\Resources\RouterResource\Pages;

use App\Filament\Resources\RouterResource;
use App\Services\RouterProvisioningService;
use Filament\Resources\Pages\CreateRecord;

class CreateRouter extends CreateRecord
{
    protected static string $resource = RouterResource::class;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return RouterResource::normalizeRouterSettings($data);
    }

    protected function afterCreate(): void
    {
        $provisioning = app(RouterProvisioningService::class);
        $server = $provisioning->radiusServerForRouter($this->record);

        if ($server !== null) {
            $provisioning->ensureNasDevice($this->record, $server);
        }
    }
}
