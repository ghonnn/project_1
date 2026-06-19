<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RadiusUser;
use App\Models\Router;
use App\Models\Service;
use App\Models\Tenant;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.dashboard';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $today = now()->toDateString();
        $tenant = Tenant::query()->where('status', 'active')->orderBy('name')->first()
            ?? Tenant::query()->orderBy('name')->first();
        $tenantId = $tenant?->id;
        $subscriptionOnline = RadiusUser::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->count();
        $activeSubscriptions = Service::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->count();
        $routerCount = Router::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->count();
        $routerActive = Router::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->count();

        return [
            'tenant' => $tenant,
            'licenseName' => $tenant?->plan ?: 'NEX BASIC',
            'timezone' => config('app.timezone'),
            'invoiceToday' => Payment::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->whereDate('paid_at', $today)
                ->whereIn('status', ['paid', 'reconciled'])
                ->sum('amount'),
            'invoiceIssuedToday' => Invoice::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->whereDate('created_at', $today)
                ->sum('total_amount'),
            'expenseToday' => 0,
            'voucherIncomeToday' => 0,
            'voucherOnline' => 0,
            'subscriptionOnline' => $subscriptionOnline,
            'isolatedCustomers' => Service::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->where('status', 'suspended')
                ->count(),
            'activeSubscriptions' => $activeSubscriptions,
            'routerCount' => $routerCount,
            'routerActive' => $routerActive,
            'maxSessions' => $tenant?->license_max_sessions ?: 250,
            'maxVouchers' => $tenant?->license_max_vouchers ?: 5000,
            'maxSubscriptions' => $tenant?->license_max_subscriptions ?: 200,
            'maxRouters' => $tenant?->license_max_routers ?: max(1, $routerCount),
            'logs' => AuditLog::query()
                ->with('user')
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->latest()
                ->limit(8)
                ->get(),
        ];
    }
}
