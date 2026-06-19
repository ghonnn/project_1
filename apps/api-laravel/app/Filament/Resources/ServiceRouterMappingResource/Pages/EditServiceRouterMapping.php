<?php

namespace App\Filament\Resources\ServiceRouterMappingResource\Pages;

use App\Filament\Resources\ServiceRouterMappingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceRouterMapping extends EditRecord
{
    protected static string $resource = ServiceRouterMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
