<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class ServiceCategoryPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'service.manage';
    }
}
