<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class InvoicePolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'billing.manage';
    }
}
