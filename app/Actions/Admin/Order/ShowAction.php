<?php

namespace App\Actions\Admin\Order;

use App\Models\OrderDeliveryInYandex;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class ShowAction
{
    public function __invoke($order): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $order->load('deliveryInYandex.user');

        $deliveryPrice = $order->items->where('product_name', '=', 'Доставка')->pluck('product_price')->first();
        $count_of_orders_to_yandex_awaiting_estimate = $order->deliveryInYandex
            ->where('status', '!=', OrderDeliveryInYandex::YANDEX_STATUS_READY_FOR_APPROVAL)
            ->where('offer_price', '=', '0')
            ->count();

        return view('admin.orders.show', compact('order', 'deliveryPrice', 'count_of_orders_to_yandex_awaiting_estimate'));
    }
}
