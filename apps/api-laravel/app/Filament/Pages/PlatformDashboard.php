<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use App\Models\Router;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlatformDashboard extends Page
{
    protected static ?string $navigationGroup = 'Platform';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Dashboard Platform';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.platform-dashboard';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'tenantTotal' => Tenant::query()->count(),
            'tenantActive' => Tenant::query()->where('status', 'active')->count(),
            'tenantSuspended' => Tenant::query()->where('status', 'suspended')->count(),
            'userTotal' => User::query()->count(),
            'serviceTotal' => Service::query()->count(),
            'onlineTotal' => Schema::hasTable('radacct')
                ? DB::table('radacct')->whereNull('acctstoptime')->distinct('username')->count('username')
                : 0,
            'routerTotal' => Router::query()->count(),
            'invoiceTotal' => Invoice::query()->sum('total_amount'),
            'tenants' => Tenant::query()->latest()->limit(12)->get(),
        ];
    }
}
