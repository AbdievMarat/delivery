<?php

namespace App\Actions\Admin\Order;

use App\Enums\DeliveryMode;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderDeliveryInYandex;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class IndexAction
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
            ->orderByDesc('orders.id')
            ->paginate(10)
            ->withQueryString();

        foreach ($orders as $order) {
            $order->totalProcessingTime = $order->calculateTotalProcessingTime();
        }

        $deliveryModes = DeliveryMode::values();
        $sources = OrderSource::values();
        $countries = Country::query()->pluck('name', 'id')->all();
        $statuses = OrderStatus::values();
        $paymentStatuses = PaymentStatus::values();

        return view('admin.orders.index', compact('orders', 'deliveryModes', 'sources', 'countries', 'statuses', 'paymentStatuses'));
    }
}
