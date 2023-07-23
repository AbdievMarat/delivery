<?php

namespace App\Actions\Admin\Order;

use App\Enums\OrderStatus;
use App\Enums\ShopStatus;
use App\Models\OrderDeliveryInYandex;
use App\Models\Shop;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class EditAction
{
    public function __invoke($order): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $order->load('deliveryInYandex.user');

        $statuses = [
            OrderStatus::New->value => OrderStatus::New->value,
            OrderStatus::InShop->value => OrderStatus::InShop->value,
            OrderStatus::AtDriver->value => OrderStatus::AtDriver->value,
            OrderStatus::Delivered->value => OrderStatus::Delivered->value,
        ];
        $shops = Shop::query()
            ->where('country_id', '=', $order->country_id)
            ->where('status', '=', ShopStatus::Active)
            ->pluck('name', 'id')
            ->all();
        $deliveryPrice = $order->items->where('product_name', '=', 'Доставка')->pluck('product_price')->first();
        $count_of_orders_to_yandex_awaiting_estimate = $order->deliveryInYandex
            ->where('status', '!=', OrderDeliveryInYandex::YANDEX_STATUS_READY_FOR_APPROVAL)
            ->where('offer_price', '=', '0')
            ->count();

        return view('admin.orders.edit', compact('order', 'statuses', 'shops', 'deliveryPrice', 'count_of_orders_to_yandex_awaiting_estimate'));
    }
}
