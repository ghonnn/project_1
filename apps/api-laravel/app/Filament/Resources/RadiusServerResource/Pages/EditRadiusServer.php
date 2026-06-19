<?php

namespace App\Filament\Resources\RadiusServerResource\Pages;

use App\Filament\Resources\RadiusServerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRadiusServer extends EditRecord
{
    protected static string $resource = RadiusServerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
