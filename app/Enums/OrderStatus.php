<?php

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'Новый';
    case InShop = 'В магазине';
    case AtDriver = 'У курьера';
    case Delivered = 'Доставлен';
    case Canceled = 'Отменен';

    public static function values(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }

    public function colorClass(): string
    {
        return match($this) {
            OrderStatus::New => 'bg-dark',
            OrderStatus::InShop => 'bg-primary',
            OrderStatus::AtDriver => 'bg-warning',
            OrderStatus::Delivered => 'bg-success',
            OrderStatus::Canceled => 'bg-danger',
        };
    }
}
