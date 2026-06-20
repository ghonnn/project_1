<?php

namespace App\Filament\Pages;

use App\Filament\Support\ComingSoonPage;

class AdminUsers extends ComingSoonPage
{
    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Admin';

    protected static ?string $title = 'Admin & Staff';

    protected static ?int $navigationSort = 30;

    protected string $description = 'Manajemen user staff/admin internal dan hak akses (role & permission), terhubung ke model Role/Permission yang sudah ada di database.';

    /** @var array<int, string> */
    protected array $plannedFeatures = [
        'CRUD user admin/staff per tenant',
        'Assign role & permission ke user',
        'Riwayat login (terkait Audit Log)',
    ];
}
