<?php

namespace App\Filament\Pages;

use App\Filament\Support\ComingSoonPage;

class GenieAcs extends ComingSoonPage
{
    protected static ?string $navigationGroup = 'Jaringan';

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static ?string $navigationLabel = 'GenieACS';

    protected static ?string $title = 'GenieACS';

    protected static ?int $navigationSort = 70;

    protected string $description = 'Integrasi TR-069 ACS (GenieACS) untuk monitoring dan manajemen ONU/CPE pelanggan secara remote (reboot, reset, summon, sinkronisasi).';

    /** @var array<int, string> */
    protected array $plannedFeatures = [
        'Daftar ONU: status online/offline, RX power, SSID, uptime, last inform',
        'Aksi remote: summon, reboot, reset, sinkron data dari ACS server',
        'Manajemen multi server GenieACS per tenant',
    ];
}
