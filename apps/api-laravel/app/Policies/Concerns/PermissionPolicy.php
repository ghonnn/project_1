<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class PermissionPolicy
{
    abstract protected function permission(): string;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission($this->permission());
    }

    public function view(User $user, Model $model): bool
    {
        return $user->hasPermission($this->permission());
    }

    public function create(User $user): bool
    {
        return $user->hasPermission($this->permission());
    }

    public function update(User $user, Model $model): bool
    {
        return $user->hasPermission($this->permission());
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->hasPermission($this->permission());
    }
}
