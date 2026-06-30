<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class TenantPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'tenant.manage';
    }
}
