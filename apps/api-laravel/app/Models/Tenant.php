<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends NexModel
{
    protected function casts(): array
    {
        return [
            'billing_settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
