<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class MitraPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'mitra.manage';
    }
}
