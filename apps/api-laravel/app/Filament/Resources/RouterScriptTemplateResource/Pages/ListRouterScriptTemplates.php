<?php

namespace App\Filament\Resources\RouterScriptTemplateResource\Pages;

use App\Filament\Resources\RouterScriptTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRouterScriptTemplates extends ListRecords
{
    protected static string $resource = RouterScriptTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
