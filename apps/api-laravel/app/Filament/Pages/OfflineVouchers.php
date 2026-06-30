<?php

namespace App\Filament\Pages;

use App\Filament\Support\VoucherPage;

class OfflineVouchers extends VoucherPage
{
    protected string $pageType = 'offline';

    protected static ?string $navigationLabel = 'Voucher offline';

    protected static ?int $navigationSort = 45;
}
