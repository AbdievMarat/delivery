<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Paid = 'Оплаченный';
    case Unpaid = 'Неоплаченный';

    public static function values(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }

    public function colorClass(): string
    {
        return match($this) {
            PaymentStatus::Paid => 'bg-success',
            PaymentStatus::Unpaid => 'bg-danger',
        };
    }
}
