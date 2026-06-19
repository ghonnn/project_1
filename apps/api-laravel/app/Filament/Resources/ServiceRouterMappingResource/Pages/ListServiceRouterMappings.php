<?php

namespace App\Filament\Resources\ServiceRouterMappingResource\Pages;

use App\Filament\Resources\ServiceRouterMappingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceRouterMappings extends ListRecords
{
    protected static string $resource = ServiceRouterMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
