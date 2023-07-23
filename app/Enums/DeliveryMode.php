<?php

namespace App\Enums;

enum DeliveryMode: string
{
    case SoonAsPossible = 'Как можно скорее';
    case OnSpecifiedDate = 'В указанную дату';

    public static function values(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }
}
