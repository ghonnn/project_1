<?php

namespace App\Filament\Support;

use Filament\Pages\Page;

abstract class VoucherPage extends Page
{
    protected static ?string $navigationGroup = 'Voucher';

    protected static ?string $navigationIcon = 'heroicon-o-wifi';

    protected static string $view = 'filament.pages.voucher-module';

    protected string $pageType = 'profile';

    /**
     * @return array<string, string>
     */
    protected function getViewData(): array
    {
        return [
            'title' => static::getNavigationLabel(),
            'pageType' => $this->pageType,
        ];
    }

    public function headingTitle(): string
    {
        return match ($this->pageType) {
            'stock' => 'Stok Voucher',
            'sold' => 'Voucher Terjual',
            'online' => 'Voucher Online',
            'recap' => 'Rekap Voucher',
            'template' => 'Template Voucher',
            default => 'Profil Voucher',
        };
    }

    /** @return array<int, array{label: string, value: string, icon: string, color: string}> */
    public function stats(): array
    {
        return match ($this->pageType) {
            'stock' => [
                ['label' => 'Total Stok', 'value' => '0', 'icon' => 'heroicon-o-wifi', 'color' => '#0ea5e9'],
                ['label' => 'Total HPP', 'value' => 'Rp0', 'icon' => 'heroicon-o-banknotes', 'color' => '#22c55e'],
                ['label' => 'Total Komisi', 'value' => 'Rp0', 'icon' => 'heroicon-o-currency-dollar', 'color' => '#f59e0b'],
                ['label' => 'Total Harga', 'value' => 'Rp0', 'icon' => 'heroicon-o-circle-stack', 'color' => '#06b6d4'],
            ],
            'sold' => [
                ['label' => 'Jumlah Terjual', 'value' => '0', 'icon' => 'heroicon-o-shopping-cart', 'color' => '#0ea5e9'],
                ['label' => 'Total Penjualan', 'value' => 'Rp0', 'icon' => 'heroicon-o-banknotes', 'color' => '#22c55e'],
                ['label' => 'Total '.now()->translatedFormat('F Y'), 'value' => 'Rp0', 'icon' => 'heroicon-o-calendar-days', 'color' => '#06b6d4'],
                ['label' => 'Jumlah Expired', 'value' => '0', 'icon' => 'heroicon-o-calendar-date-range', 'color' => '#ef4444'],
            ],
            default => [],
        };
    }

    /** @return array<int, array{label: string, color: string, modal?: string}> */
    public function toolbarActions(): array
    {
        return match ($this->pageType) {
            'profile' => [
                ['label' => 'Menu', 'color' => '#0ea5e9', 'modal' => 'profile-menu'],
                ['label' => 'Tambah', 'color' => '#22c55e', 'modal' => 'profile-form'],
            ],
            'stock' => [
                ['label' => 'Menu', 'color' => '#0ea5e9', 'modal' => 'stock-menu'],
                ['label' => 'Print', 'color' => '#64748b', 'modal' => 'print-voucher'],
                ['label' => 'Buat User', 'color' => '#22c55e', 'modal' => 'create-user'],
                ['label' => 'Buat Voucher', 'color' => '#0ea5e9', 'modal' => 'create-user'],
                ['label' => 'Outlet', 'color' => '#64748b', 'modal' => 'outlet'],
                ['label' => 'Setting', 'color' => '#ef4444', 'modal' => 'hotspot-setting'],
                ['label' => 'Import', 'color' => '#0ea5e9', 'modal' => 'import-voucher'],
                ['label' => 'Export', 'color' => '#22c55e', 'modal' => 'export-voucher'],
            ],
            'sold' => [
                ['label' => 'Menu', 'color' => '#0ea5e9', 'modal' => 'sold-menu'],
                ['label' => 'Export', 'color' => '#06b6d4', 'modal' => 'export-data'],
                ['label' => 'Rekapitulasi', 'color' => '#22c55e', 'modal' => 'recap-sale'],
                ['label' => 'Hapus Expired', 'color' => '#ef4444', 'modal' => 'delete-expired'],
            ],
            default => [
                ['label' => 'Menu', 'color' => '#0ea5e9'],
                ['label' => 'Export', 'color' => '#22c55e'],
            ],
        };
    }

    /** @return array<int, string> */
    public function filters(): array
    {
        return match ($this->pageType) {
            'profile' => ['13', 'AKTIF', 'Search...'],
            'stock', 'sold' => ['10', 'Cari partner', 'ALL ROUTER', 'ALL PROFILE', 'Tgl pembuatan', 'Cari voucher...'],
            default => ['10', 'Search...'],
        };
    }

    /** @return array<int, string> */
    public function columns(): array
    {
        return match ($this->pageType) {
            'profile' => ['ID', 'NAMA PROFILE', 'GROUP', 'ADDRESSLIST', 'RATE LIMIT', 'SHARED', 'KUOTA', 'DURASI', 'AKTIF', 'HPP', 'KOMISI', 'HARGA', 'JML.VC'],
            'stock' => ['USERNAME', 'PASSWORD', 'PROFILE', 'ROUTER', 'SERVER', 'PARTNER', 'OUTLET', 'HPP', 'KOMISI', 'HARGA', 'SALDO', 'ADMIN', 'KODE', 'TGL PEMBUATAN', 'MAC'],
            'sold' => ['USERNAME', 'PASSWORD', 'PROFILE', 'ROUTER', 'SERVER', 'PARTNER', 'OUTLET', 'HPP', 'KOMISI', 'HARGA', 'SALDO', 'ADMIN', 'KODE', 'DURASI', 'KUOTA', 'TGL AKTIF', 'TGL EXPIRED', 'MAC ADDRESS', 'MAC'],
            'online' => ['USERNAME', 'PROFILE', 'IP ADDRESS', 'MAC ADDRESS', 'UPTIME', 'UPLOAD', 'DOWNLOAD', 'ROUTER', 'SERVER', 'PARTNER'],
            'recap' => ['TANGGAL', 'PARTNER', 'OUTLET', 'PROFILE', 'QTY', 'HPP', 'KOMISI', 'HARGA', 'TOTAL'],
            default => ['NAMA TEMPLATE', 'HOTSPOT', 'DNS', 'PHONE', 'STATUS'],
        };
    }
}
