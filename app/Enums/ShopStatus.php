<?php

namespace App\Enums;

enum ShopStatus: string
{
    case Active = 'Активный';
    case Inactive = 'Неактивный';

    public static function values(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }
}
