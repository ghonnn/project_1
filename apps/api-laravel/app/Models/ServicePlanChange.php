<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePlanChange extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return ['change_date' => 'date'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function oldProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'old_product_id');
    }

    public function newProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'new_product_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    protected static function booted(): void
    {
        static::saved(function (ServicePlanChange $change): void {
            $service = $change->service()->first();
            $newProduct = $change->newProduct()->first();

            if (! $service || ! $newProduct) {
                return;
            }

            $service->update([
                'product_id' => $newProduct->id,
                'service_category_id' => $newProduct->service_category_id,
                'billing_profile_name' => $newProduct->name,
                'billing_cycle' => $newProduct->billing_cycle,
                'profile_price' => $newProduct->price,
            ]);
        });
    }
}
