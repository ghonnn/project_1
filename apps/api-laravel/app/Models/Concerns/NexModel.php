<?php

namespace App\Models\Concerns;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

abstract class NexModel extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }
}
