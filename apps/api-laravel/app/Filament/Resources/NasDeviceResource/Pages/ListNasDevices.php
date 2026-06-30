<?php

namespace App\Filament\Resources\NasDeviceResource\Pages;

use App\Filament\Resources\NasDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNasDevices extends ListRecords
{
    protected static string $resource = NasDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
