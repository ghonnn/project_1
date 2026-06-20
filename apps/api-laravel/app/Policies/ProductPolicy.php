<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class ProductPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'service.manage';
    }
}
