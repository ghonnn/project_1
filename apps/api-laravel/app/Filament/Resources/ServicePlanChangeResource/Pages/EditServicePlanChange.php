<?php

namespace App\Filament\Resources\ServicePlanChangeResource\Pages;

use App\Filament\Resources\ServicePlanChangeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServicePlanChange extends EditRecord
{
    protected static string $resource = ServicePlanChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
