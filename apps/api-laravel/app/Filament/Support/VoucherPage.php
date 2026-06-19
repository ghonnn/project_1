<?php

namespace App\Filament\Support;

use Filament\Pages\Page;

abstract class VoucherPage extends Page
{
    protected static ?string $navigationGroup = 'Voucher';

    protected static ?string $navigationIcon = 'heroicon-o-wifi';

    protected static string $view = 'filament.pages.voucher-placeholder';

    /**
     * @return array<string, string>
     */
    protected function getViewData(): array
    {
        return ['title' => static::getNavigationLabel()];
    }
}
