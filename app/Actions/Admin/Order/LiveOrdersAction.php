<?php

namespace App\Actions\Admin\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderDeliveryInYandex;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class LiveOrdersAction
{
    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function __invoke(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $orders = Order::with([
            'deliveryInYandex' => function ($query) {
                $query->where('status', '!=', OrderDeliveryInYandex::YANDEX_STATUS_CANCELLED);
            },
            'shop', 'operator'])
            ->select("orders.*", "countries.name AS country_name")
            ->join("countries", "countries.id", "=", "orders.country_id")
            ->filter()
            ->whereNotIn('orders.status', [OrderStatus::Delivered->value, OrderStatus::Canceled->value])
            ->orderBy('orders.payment_status', 'ASC')
            ->orderByDesc('orders.id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.live', compact('orders'));
    }
}
