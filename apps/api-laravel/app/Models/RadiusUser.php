<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiusUser extends NexModel
{
    use BelongsToTenant;

    protected $hidden = ['secret'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(RadiusProfile::class, 'profile_id');
    }
}
