<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Customer extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'billing_contact' => 'array',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer): void {
            if (! $customer->type) {
                $customer->type = 'individual';
            }

            if (! $customer->status) {
                $customer->status = 'active';
            }

            if (! $customer->customer_number) {
                $last = static::query()
                    ->where('tenant_id', $customer->tenant_id)
                    ->whereNotNull('customer_number')
                    ->orderByDesc('customer_number')
                    ->value('customer_number');

                $customer->customer_number = str_pad((string) (((int) $last) + 1), 7, '0', STR_PAD_LEFT);
            }

            if (! $customer->client_area_url) {
                $customer->client_area_url = url('/login/'.base64_encode((string) Str::uuid()));
            }
        });
    }
}
