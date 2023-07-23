<?php

namespace App\Enums;

enum CountryStatus: string
{
    case Active = 'Активный';
    case Inactive = 'Неактивный';

    public static function values(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }
}
