<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class AuditLogPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'audit.read';
    }
}
