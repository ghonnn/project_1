<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class NasDevicePolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'radius.manage';
    }
}
