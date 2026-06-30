<?php

namespace App\Filament\Pages;

use App\Filament\Support\VoucherPage;

class OnlineVouchers extends VoucherPage
{
    protected string $pageType = 'online';

    protected static ?string $navigationLabel = 'Voucher online';

    protected static ?int $navigationSort = 40;
}
