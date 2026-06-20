<?php

namespace App\Filament\Pages;

use App\Filament\Support\ComingSoonPage;

class MapCustomers extends ComingSoonPage
{
    protected static ?string $navigationGroup = 'Map';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Map Pelanggan';

    protected static ?string $title = 'Map Pelanggan';

    protected static ?int $navigationSort = 10;

    protected string $description = 'Menampilkan lokasi pelanggan pada peta berdasarkan latitude/longitude di Service, dikelompokkan per status layanan dan router.';

    /** @var array<int, string> */
    protected array $plannedFeatures = [
        'Pin lokasi pelanggan dengan warna berbeda per status (aktif/suspend/terminasi)',
        'Filter berdasarkan router, region, dan status layanan',
        'Klik pin untuk lihat ringkasan layanan dan link ke Service Detail',
    ];
}
