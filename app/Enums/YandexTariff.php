<?php

namespace App\Enums;

enum YandexTariff: string
{
    case Courier = 'courier';
    case Express = 'express';
    case Cargo = 'cargo';

    public static function values(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }
}
