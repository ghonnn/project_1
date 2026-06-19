<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return ['billing_contact' => 'array'];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
