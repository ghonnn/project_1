<?php

namespace App\Filament\Pages;

use App\Filament\Support\VoucherPage;

class SoldVouchers extends VoucherPage
{
    protected string $pageType = 'sold';

    protected static ?string $navigationLabel = 'Voucher terjual';

    protected static ?int $navigationSort = 30;
}
