<?php

namespace App\Filament\Resources\RadiusUserResource\Pages;

use App\Filament\Resources\RadiusUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRadiusUsers extends ListRecords
{
    protected static string $resource = RadiusUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
