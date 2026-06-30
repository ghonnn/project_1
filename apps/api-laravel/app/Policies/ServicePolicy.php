<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class ServicePolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'service.manage';
    }
}
