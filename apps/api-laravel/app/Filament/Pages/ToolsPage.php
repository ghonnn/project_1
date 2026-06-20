<?php

namespace App\Filament\Pages;

use App\Filament\Support\ComingSoonPage;

class ToolsPage extends ComingSoonPage
{
    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Tools';

    protected static ?string $title = 'Tools';

    protected static ?int $navigationSort = 20;

    protected string $description = 'Kumpulan utility jaringan, contohnya Hurricane Electric BGP Toolkit untuk membuat Mikrotik address-list dari AS Number (policy based routing).';

    /** @var array<int, string> */
    protected array $plannedFeatures = [
        'BGP toolkit: ambil IP range dari AS Number, generate script Mikrotik address-list',
        'Utility lain sesuai kebutuhan NOC (ping/traceroute helper, dst.)',
    ];
}
