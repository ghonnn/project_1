<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouterInterface extends NexModel
{
    use BelongsToTenant;

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }
}
