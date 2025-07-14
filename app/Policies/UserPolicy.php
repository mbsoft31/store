<?php
// app/Policies/UserPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRole;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::OWNER, UserRole::MANAGER]);
    }

    public function view(User $user, User $model): bool
    {
        return $user->tenant_id === $model->tenant_id && 
               in_array($user->role, [UserRole::OWNER, UserRole::MANAGER]);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::OWNER;
    }

    public function update(User $user, User $model): bool
    {
        return $user->tenant_id === $model->tenant_id && 
               $user->role === UserRole::OWNER &&
               $user->id !== $model->id; // Can't modify own role
    }

    public function delete(User $user, User $model): bool
    {
        return $user->tenant_id === $model->tenant_id && 
               $user->role === UserRole::OWNER &&
               $user->id !== $model->id; // Can't delete self
    }
}
