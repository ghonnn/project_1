<?php

namespace App\Filament\Pages;

use App\Filament\Support\ComingSoonPage;

class Olt extends ComingSoonPage
{
    protected static ?string $navigationGroup = 'Jaringan';

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'OLT';

    protected static ?string $title = 'Data OLT';

    protected static ?int $navigationSort = 60;

    protected string $description = 'Inventaris OLT (Optical Line Terminal) sebagai sumber sinyal fiber: vendor, model, IP remote, dan parameter SNMP untuk monitoring.';

    /** @var array<int, string> */
    protected array $plannedFeatures = [
        'Data master OLT: nama, vendor, model, jenis, IP remote, port',
        'Konfigurasi SNMP community read/write per OLT',
        'Mapping OLT ke ODC/ODP turunannya untuk impact analysis',
    ];
}
