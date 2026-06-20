<?php

namespace App\Policies;

use App\Policies\Concerns\PermissionPolicy;

class TicketPolicy extends PermissionPolicy
{
    protected function permission(): string
    {
        return 'ticket.manage';
    }
}
