<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends NexModel
{
    use BelongsToTenant;

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
