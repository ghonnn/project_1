<?php

namespace App\Filament\Pages;

use App\Filament\Support\VoucherPage;

class VoucherTemplate extends VoucherPage
{
    protected string $pageType = 'template';

    protected static ?string $navigationLabel = 'Template';

    protected static ?int $navigationSort = 60;
}
