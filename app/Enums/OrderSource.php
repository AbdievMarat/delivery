<?php

namespace App\Enums;

enum OrderSource: string
{
    case MobileApp = 'Мобильное приложение';
    case Other = 'Прочее';

    public static function values(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }
}
