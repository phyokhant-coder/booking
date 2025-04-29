<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case CANCEL = 'CANCEL';
    case SHIPPED = 'SHIPPED';
    case DELIVERED = 'DELIVERED';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'PENDING',
            self::CONFIRMED => 'CONFIRMED',
            self::CANCEL => 'CANCEL',
            self::SHIPPED => 'SHIPPED',
            self::DELIVERED => 'DELIVERED',
        };
    }
}
