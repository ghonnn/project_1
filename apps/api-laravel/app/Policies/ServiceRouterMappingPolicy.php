<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class ServiceRouterMappingPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'router.manage';
    }
}
