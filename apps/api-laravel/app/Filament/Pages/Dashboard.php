<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\RadiusUser;
use App\Models\Router;
use App\Models\Service;
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

        return [
            'invoiceToday' => Invoice::query()->whereDate('created_at', $today)->sum('total_amount'),
            'expenseToday' => 0,
            'voucherIncomeToday' => 0,
            'voucherOnline' => 0,
            'subscriptionOnline' => RadiusUser::query()->where('status', 'active')->count(),
            'isolatedCustomers' => Service::query()->where('status', 'suspended')->count(),
            'activeSubscriptions' => Service::query()->where('status', 'active')->count(),
            'routerCount' => Router::query()->count(),
            'routerActive' => Router::query()->where('status', 'active')->count(),
            'logs' => AuditLog::query()->with('user')->latest()->limit(8)->get(),
        ];
    }
}
