<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromArray, WithHeadings, ShouldAutoSize, WithMapping
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Заказ №',
            'Дата',
            'Доставка',
            'Источник',
            'Страна',
            'Магазин',
            'Оператор',
            'Клиент',
            'Номер',
            'Адрес',
            'Детали',
            'Сумма',
            'Оплачено деньгами',
            'Оплачено бонусами',
            'Стоимость доставки',
            'Потрачено в Яндекс',
            'Валюта',
            'Статус оплаты',
            'Статус',
        ];
    }

    public function map($row): array
    {
        return [
            $row['order_number'],
            $row['created_at'],
            $row['delivery_mode'],
            $row['source'],
            $row['country_name'],
            $row['shop_name'],
            $row['operator_name'],
            $row['client_name'],
            $row['client_phone'],
            $row['address'],
            $row['items'],
            $row['order_price'],
            $row['payment_cash'],
            $row['payment_bonuses'],
            $row['delivery_price'],
            $row['spent_in_yandex'],
            $row['currency_name'],
            $row['payment_status'],
            $row['status'],
        ];
    }
}
