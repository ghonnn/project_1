<?php

namespace App\Filament\Pages;

use App\Filament\Support\ComingSoonPage;

class WhatsappSetting extends ComingSoonPage
{
    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'WhatsApp';

    protected static ?string $title = 'WhatsApp Gateway';

    protected static ?int $navigationSort = 10;

    protected string $description = 'Pengaturan gateway WhatsApp untuk notifikasi otomatis ke pelanggan/mitra (invoice, jatuh tempo, isolir, voucher terjual).';

    /** @var array<int, string> */
    protected array $plannedFeatures = [
        'Konfigurasi koneksi device/API WhatsApp gateway',
        'Template pesan: invoice baru, reminder jatuh tempo, isolir, pembayaran diterima',
        'Log pengiriman pesan per pelanggan',
    ];
}
