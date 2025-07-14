<?php
// app/Policies/ProductPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;
use App\Enums\UserRole;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view products
    }

    public function view(User $user, Product $product): bool
    {
        return $user->tenant_id === $product->tenant_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::OWNER, UserRole::MANAGER]);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->tenant_id === $product->tenant_id &&
               in_array($user->role, [UserRole::OWNER, UserRole::MANAGER]);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->tenant_id === $product->tenant_id &&
               in_array($user->role, [UserRole::OWNER, UserRole::MANAGER]);
    }
}
