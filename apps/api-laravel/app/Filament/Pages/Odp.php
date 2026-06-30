<?php

namespace App\Filament\Pages;

use App\Filament\Support\ComingSoonPage;

class Odp extends ComingSoonPage
{
    protected static ?string $navigationGroup = 'Jaringan';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'ODP';

    protected static ?string $title = 'ODP (Optical Distribution Point)';

    protected static ?int $navigationSort = 50;

    protected string $description = 'Inventaris ODP/ODC sebagai perangkat distribusi fiber sebelum ke pelanggan: jumlah port, port terisi, kode, dan lokasi terpasang.';

    /** @var array<int, string> */
    protected array $plannedFeatures = [
        'Data master ODP: kode, merk, jumlah port, ODC induk, wilayah',
        'Tracking port terisi/kosong dan okupansi otomatis dari Service Mapping',
        'Riwayat penambahan/perubahan ODP',
    ];
}
