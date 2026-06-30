<?php

namespace App\Filament\Pages;

use App\Filament\Support\ComingSoonPage;

class MapOdp extends ComingSoonPage
{
    protected static ?string $navigationGroup = 'Map';

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Map ODP';

    protected static ?string $title = 'Map ODP';

    protected static ?int $navigationSort = 20;

    protected string $description = 'Menampilkan lokasi ODP (Optical Distribution Point) pada peta beserta okupansi port, untuk membantu survey dan perencanaan instalasi FTTH.';

    /** @var array<int, string> */
    protected array $plannedFeatures = [
        'Pin ODP dengan indikator okupansi (kosong/terisi/penuh)',
        'Pencarian ODP berdasarkan wilayah',
        'Integrasi dengan modul ODP untuk lihat detail port',
    ];
}
