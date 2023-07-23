<?php

namespace App\Actions\Admin\Order;

use Illuminate\Http\JsonResponse;

class GetOrdersInYandexAction
{
    public function __invoke($order): JsonResponse
    {
        $order->load('deliveryInYandex.user');
        $ordersInYandex = $order->deliveryInYandex;
        $deliveryPrice = $order->items->where('product_name', '=', 'Доставка')->pluck('product_price')->first();

        return response()->json([
            'content' => view('admin.orders.orders-in-yandex', [
                'ordersInYandex' => $ordersInYandex,
                'deliveryPrice' => $deliveryPrice
            ])->render()
        ]);
    }
}
