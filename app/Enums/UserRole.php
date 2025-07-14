<?php
// app/Enums/UserRole.php

namespace App\Enums;

enum UserRole: string
{
    case OWNER = 'owner';
    case MANAGER = 'manager';
    case CASHIER = 'cashier';

    public function getLabel(): string
    {
        return match ($this) {
            self::OWNER => 'Store Owner',
            self::MANAGER => 'Manager',
            self::CASHIER => 'Cashier',
        };
    }

    public function getPermissions(): array
    {
        return match ($this) {
            self::OWNER => ['*'],
            self::MANAGER => ['products.*', 'inventory.*', 'orders.*'],
            self::CASHIER => ['orders.create', 'orders.read', 'products.read'],
        };
    }
}