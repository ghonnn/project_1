<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Service extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'terminated_at' => 'datetime',
            'billing_active_date' => 'date',
            'billing_isolation_date' => 'date',
            'invoice_issue_date' => 'date',
            'installed_at' => 'date',
            'ppn_enabled' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'dpp_amount' => 'decimal:2',
            'ppn_rate' => 'decimal:2',
            'profile_price' => 'decimal:2',
            'partner_commission' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function routerMappings(): HasMany
    {
        return $this->hasMany(ServiceRouterMapping::class);
    }

    public function radiusUsers(): HasMany
    {
        return $this->hasMany(RadiusUser::class);
    }

    public function planChanges(): HasMany
    {
        return $this->hasMany(ServicePlanChange::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(ServiceAddon::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service): void {
            static::applyBillingDefaults($service);
        });

        static::creating(function (Service $service): void {
            if ($service->cid) {
                return;
            }

            $date = Carbon::now()->format('Y_m_d');
            $prefix = $date.'_';
            $lastCid = static::query()
                ->where('tenant_id', $service->tenant_id)
                ->where('cid', 'like', $prefix.'%')
                ->orderByDesc('cid')
                ->value('cid');

            $sequence = 1;
            if (is_string($lastCid)) {
                $sequence = ((int) substr($lastCid, -5)) + 1;
            }

            $service->cid = $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
        });
    }

    private static function applyBillingDefaults(Service $service): void
    {
        if (! $service->tenant_id || ! $service->billing_active_date) {
            return;
        }

        $settings = Tenant::query()->find($service->tenant_id)?->billing_settings ?? [];
        $active = $service->billing_active_date instanceof Carbon
            ? $service->billing_active_date->copy()
            : Carbon::parse($service->billing_active_date);
        $isolationDay = max(1, min(31, (int) ($settings['monthly_isolation_day'] ?? 15)));
        $publishBeforeDays = max(0, (int) ($settings['invoice_publish_day'] ?? 10));

        $isolationDate = $active->copy()->day(min($isolationDay, $active->daysInMonth));

        if ($isolationDate->lt($active->copy()->startOfDay())) {
            $nextMonth = $active->copy()->addMonthNoOverflow();
            $isolationDate = $nextMonth->copy()->day(min($isolationDay, $nextMonth->daysInMonth));
        }

        if (! $service->billing_isolation_date || $service->isDirty('billing_active_date') || $service->isDirty('tenant_id')) {
            $service->billing_isolation_date = $isolationDate->toDateString();
        }

        if (! $service->invoice_issue_date || $service->isDirty('billing_isolation_date') || $service->isDirty('billing_active_date') || $service->isDirty('tenant_id')) {
            $service->invoice_issue_date = Carbon::parse($service->billing_isolation_date)->subDays($publishBeforeDays)->toDateString();
        }

        if (! $service->suspended_at || $service->isDirty('billing_isolation_date') || $service->isDirty('billing_active_date') || $service->isDirty('tenant_id')) {
            $service->suspended_at = $service->billing_isolation_date;
        }

        $service->ppn_rate = (float) ($settings['ppn_rate'] ?? ($service->ppn_rate ?: 11));
    }
}
