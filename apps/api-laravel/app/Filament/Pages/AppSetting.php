<?php

namespace App\Filament\Pages;

use App\Filament\Support\ComingSoonPage;

class AppSetting extends ComingSoonPage
{
    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Setting';

    protected static ?string $title = 'Pengaturan Aplikasi';

    protected static ?int $navigationSort = 5;

    protected string $description = 'Pengaturan branding aplikasi per tenant: logo/favicon, nama perusahaan, tampilan invoice, custom domain, dan integrasi payment gateway/rekonsiliasi bank.';

    /** @var array<int, string> */
    protected array $plannedFeatures = [
        'Logo light/dark, favicon, nama perusahaan, slogan, alamat, WhatsApp CS',
        'Tampilan invoice & laporan: kop, watermark, tanda tangan, catatan paid/unpaid',
        'Custom domain via Cloudflare (subdomain, A record, custom domain)',
        'Integrasi rekonsiliasi pembayaran: MutasiBank.co.id, Moota.co, payment gateway',
    ];
}
