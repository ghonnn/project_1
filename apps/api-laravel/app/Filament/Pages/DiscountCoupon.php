<?php

namespace App\Filament\Pages;

use App\Filament\Support\FinanceTablePage;

class DiscountCoupon extends FinanceTablePage
{
    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Kupon diskon';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 14;

    public function tableTitle(): string
    {
        return 'Kupon Diskon';
    }

    public function toolbarActions(): array
    {
        return [
            ['label' => 'Print', 'color' => 'gray'],
            ['label' => 'Hapus', 'color' => 'danger'],
            ['label' => 'Tambah', 'color' => 'info'],
            ['label' => 'Export', 'color' => 'info'],
            ['label' => 'Kosongkan', 'color' => 'info'],
        ];
    }

    public function columns(): array
    {
        return ['NAMA KUPON', 'KODE KUPON', 'DISKON', 'MIN TRX', 'BERLAKU', 'EXPIRED', 'DIGUNAKAN', 'INVOICE', 'TGL PEMBUATAN'];
    }

    public function rows(): array
    {
        return [];
    }
}
