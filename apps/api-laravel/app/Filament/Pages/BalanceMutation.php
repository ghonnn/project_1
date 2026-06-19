<?php

namespace App\Filament\Pages;

use App\Filament\Support\FinanceTablePage;

class BalanceMutation extends FinanceTablePage
{
    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Mutasi saldo';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?int $navigationSort = 13;

    public function tableTitle(): string
    {
        return 'Mutasi Saldo Mitra';
    }

    public function toolbarActions(): array
    {
        return [
            ['label' => 'Print', 'color' => 'gray'],
            ['label' => 'Export', 'color' => 'info'],
            ['label' => 'Kosongkan', 'color' => 'danger'],
        ];
    }

    public function columns(): array
    {
        return ['#', 'MITRA', 'TGL TRANSAKSI', 'KATEGORI', 'KETERANGAN', 'KREDIT', 'DEBET', 'SALDO'];
    }

    public function rows(): array
    {
        return [];
    }
}
