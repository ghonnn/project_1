<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class RouterInterfacePolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'router.manage';
    }
}
