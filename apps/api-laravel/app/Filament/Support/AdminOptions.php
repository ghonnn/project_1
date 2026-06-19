<?php

namespace App\Filament\Support;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\RadiusProfile;
use App\Models\Router;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tenant;

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
    public static function customers(): array
    {
        return Customer::query()->orderBy('name')->pluck('name', 'id')->all();
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
            ->with('customer')
            ->orderBy('cid')
            ->get()
            ->mapWithKeys(function (Service $service): array {
                $label = $service->cid ?: (($service->customer?->name ?? 'Service').' ('.$service->id.')');

                return [$service->id => $label];
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
    public static function radiusProfiles(): array
    {
        return RadiusProfile::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    public static function invoices(): array
    {
        return Invoice::query()->orderByDesc('created_at')->pluck('invoice_number', 'id')->all();
    }
}
