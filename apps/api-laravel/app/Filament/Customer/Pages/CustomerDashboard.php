<?php

namespace App\Filament\Customer\Pages;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\Ticket;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class CustomerDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Panel Pelanggan';

    protected static string $view = 'filament.customer.pages.dashboard';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = Auth::user();
        $customer = $user?->email
            ? Customer::query()->where('email', $user->email)->first()
            : null;

        $services = $customer
            ? Service::query()->with(['product', 'primaryRadiusUser', 'primaryRouterMapping.router'])->where('customer_id', $customer->id)->latest()->get()
            : collect();

        $invoices = $customer
            ? Invoice::query()->where('customer_id', $customer->id)->latest()->limit(8)->get()
            : collect();

        $tickets = $customer
            ? Ticket::query()->where('customer_id', $customer->id)->latest()->limit(8)->get()
            : collect();

        return [
            'customer' => $customer,
            'services' => $services,
            'invoices' => $invoices,
            'tickets' => $tickets,
            'activeServices' => $services->where('status', 'active')->count(),
            'unpaidTotal' => $invoices->whereIn('status', ['issued', 'overdue'])->sum('total_amount'),
        ];
    }
}
