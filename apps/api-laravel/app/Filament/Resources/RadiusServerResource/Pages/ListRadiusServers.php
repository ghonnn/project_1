<?php

namespace App\Filament\Resources\RadiusServerResource\Pages;

use App\Filament\Resources\RadiusServerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRadiusServers extends ListRecords
{
    protected static string $resource = RadiusServerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
