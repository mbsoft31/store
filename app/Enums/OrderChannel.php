<?php
// app/Enums/OrderChannel.php

namespace App\Enums;

enum OrderChannel: string
{
    case POS = 'pos';
    case ONLINE = 'online';

    public function getLabel(): string
    {
        return match ($this) {
            self::POS => 'Point of Sale',
            self::ONLINE => 'Online Store',
        };
    }
}
