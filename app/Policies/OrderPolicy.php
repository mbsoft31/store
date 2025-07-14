<?php
// app/Policies/OrderPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use App\Enums\UserRole;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view orders
    }

    public function view(User $user, Order $order): bool
    {
        return $user->tenant_id === $order->tenant_id;
    }

    public function create(User $user): bool
    {
        return true; // All authenticated users can create orders
    }

    public function refund(User $user, Order $order): bool
    {
        return $user->tenant_id === $order->tenant_id &&
               in_array($user->role, [UserRole::OWNER, UserRole::MANAGER]);
    }
}
