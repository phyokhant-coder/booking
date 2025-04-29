<?php

namespace App\Enums;

enum PromoCodeStatus: string
{
    case AVAILABLE = 'available';
    case USED = 'used';
    public function label(): string
    {
        return match($this) {
            self::AVAILABLE => 'available',
            self::USED => 'used',
        };
    }
}
