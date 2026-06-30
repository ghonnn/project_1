<?php

namespace App\Filament\Pages;

use App\Filament\Support\FinanceTablePage;

class TopupBalance extends FinanceTablePage
{
    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'TopUp saldo';

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?int $navigationSort = 12;

    public function tableTitle(): string
    {
        return 'TopUp Saldo Partner';
    }

    public function toolbarActions(): array
    {
        return [
            ['label' => 'Bayar', 'color' => 'success'],
            ['label' => 'TopUp', 'color' => 'info'],
            ['label' => 'Export', 'color' => 'info'],
            ['label' => 'Hapus', 'color' => 'danger'],
        ];
    }

    public function columns(): array
    {
        return ['INVOICE', 'PARTNER', 'PHONE', 'LEVEL', 'KATEGORI', 'TGL TOPUP', 'CARABAYAR', 'CHANNEL', 'REKENING', 'TGL BAYAR', 'ADMIN', 'TOTAL', 'NOTE'];
    }

    public function rows(): array
    {
        return [];
    }
}
