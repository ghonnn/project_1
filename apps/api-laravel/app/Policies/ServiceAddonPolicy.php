<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class ServiceAddonPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'service.manage';
    }
}
