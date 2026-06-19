<?php

namespace App\Filament\Resources\RadiusProfileResource\Pages;

use App\Filament\Resources\RadiusProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRadiusProfile extends EditRecord
{
    protected static string $resource = RadiusProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
