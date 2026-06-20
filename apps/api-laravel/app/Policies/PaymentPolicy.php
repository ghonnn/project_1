<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class PaymentPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'payment.manage';
    }
}
