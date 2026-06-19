<?php

namespace App\Filament\Resources\RouterInterfaceResource\Pages;

use App\Filament\Resources\RouterInterfaceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRouterInterfaces extends ListRecords
{
    protected static string $resource = RouterInterfaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
