<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class RadiusUserPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'radius.manage';
    }
}
