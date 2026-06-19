<?php

namespace App\Filament\Resources\OnlineSubscriptionResource\Pages;

use App\Filament\Resources\OnlineSubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListOnlineSubscriptions extends ListRecords
{
    protected static string $resource = OnlineSubscriptionResource::class;

    protected static ?string $title = 'Langganan Online';
}
