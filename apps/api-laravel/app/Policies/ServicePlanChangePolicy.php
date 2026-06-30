<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class ServicePlanChangePolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'service.manage';
    }
}
