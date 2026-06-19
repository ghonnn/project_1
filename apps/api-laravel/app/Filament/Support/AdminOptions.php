<?php

namespace App\Filament\Support;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\RadiusProfile;
use App\Models\RadiusServer;
use App\Models\Router;
use App\Models\RouterInterface;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tenant;
use App\Models\User;

class AdminOptions
{
    /**
     * @return array<string, string>
     */
    public static function tenants(): array
    {
        return Tenant::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    public static function customers(?string $tenantId = null, ?string $search = null): array
    {
        return Customer::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('customer_number', 'like', '%'.$search.'%');
            }))
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Customer $customer): array => [
                $customer->id => self::customerLabel($customer),
            ])
            ->all();
    }

    public static function customerOptionLabel(?string $customerId): ?string
    {
        $customer = $customerId ? Customer::query()->find($customerId) : null;

        return $customer ? self::customerLabel($customer) : null;
    }

    private static function customerLabel(Customer $customer): string
    {
        $parts = array_filter([
            $customer->name,
            $customer->phone,
            $customer->customer_number,
        ]);

        return implode(' - ', $parts);
    }

    /**
     * @return array<string, string>
     */
    public static function products(): array
    {
        return Product::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    public static function serviceCategories(): array
    {
        return ServiceCategory::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    public static function services(): array
    {
        return Service::query()
            ->with(['customer', 'tenant'])
            ->orderBy('cid')
            ->get()
            ->mapWithKeys(function (Service $service): array {
                $label = $service->cid ?: (($service->customer?->name ?? 'Service').' ('.$service->id.')');
                $customer = $service->customer?->name ?? 'No customer';
                $tenant = $service->tenant?->name ?? 'No tenant';

                return [$service->id => $label.' - '.$customer.' / '.$tenant];
            })
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function routers(): array
    {
        return Router::query()->orderBy('router_name')->pluck('router_name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    public static function routerInterfaces(?string $routerId = null): array
    {
        return RouterInterface::query()
            ->when($routerId, fn ($query) => $query->where('router_id', $routerId))
            ->orderBy('interface_name')
            ->get()
            ->mapWithKeys(fn (RouterInterface $interface): array => [
                $interface->id => $interface->interface_name.($interface->ip_address ? ' - '.$interface->ip_address : ''),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function radiusProfiles(): array
    {
        return RadiusProfile::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    public static function radiusServers(): array
    {
        return RadiusServer::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (RadiusServer $server): array => [
                $server->id => $server->name.' - '.$server->host,
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function invoices(): array
    {
        return Invoice::query()->orderByDesc('created_at')->pluck('invoice_number', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    public static function users(): array
    {
        return User::query()->orderBy('name')->pluck('name', 'id')->all();
    }
}
