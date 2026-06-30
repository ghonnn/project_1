<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class CustomerPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'customer.manage';
    }
}
