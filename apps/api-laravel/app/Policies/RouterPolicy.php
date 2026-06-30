<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class RouterPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'router.manage';
    }
}
