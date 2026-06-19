<?php

namespace App\Filament\Resources\ServicePlanChangeResource\Pages;

use App\Filament\Resources\ServicePlanChangeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServicePlanChanges extends ListRecords
{
    protected static string $resource = ServicePlanChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
