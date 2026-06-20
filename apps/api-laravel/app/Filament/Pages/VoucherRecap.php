<?php

namespace App\Filament\Pages;

use App\Filament\Support\VoucherPage;

class VoucherRecap extends VoucherPage
{
    protected string $pageType = 'recap';

    protected static ?string $navigationLabel = 'Rekap voucher';

    protected static ?int $navigationSort = 50;
}
