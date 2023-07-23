<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryMode;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exports\OrderExport;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderDeliveryInYandex;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderReportController extends Controller
{
    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $orders = [];
        $totalOrderPrice = 0;
        $totalPaymentCash = 0;
        $totalPaymentBonuses = 0;
        $totalDeliveryPrice = 0;
        $totalPriceInYandex = 0;

        if (!empty(Request::all())) {
            if(env('DB_CONNECTION') === 'sqlite') {
                $rawItems = "GROUP_CONCAT(order_items.product_sku || ' - ' || order_items.product_name || ' - ' || order_items.product_price || ' - ' || order_items.quantity || ' шт.', '; ') AS items";
            } else { // mysql
                $rawItems = "GROUP_CONCAT(CONCAT(order_items.product_sku, ' - ', order_items.product_name, ' - ', order_items.product_price, ' - ', order_items.quantity, ' шт.') SEPARATOR '; ') AS items";
            }

            $spentOrdersInYandex = Order::query()
                ->selectRaw('SUM(order_delivery_in_yandex.final_price) as total_price, order_delivery_in_yandex.order_id')
                ->leftJoin("order_delivery_in_yandex", "orders.id", "=", "order_delivery_in_yandex.order_id")
                ->groupBy('order_delivery_in_yandex.order_id')
                ->filter();

            $orders = Order::query()
                ->select(
                    "orders.id", "orders.order_number", "orders.created_at", "orders.delivery_mode", "orders.source", "countries.name AS country_name", "shops.name AS shop_name",
                    "users.name AS operator_name", "orders.client_name", "orders.client_phone", "orders.address", "orders.order_price", "orders.payment_cash",
                    "orders.payment_bonuses", "countries.currency_name", "orders.payment_status", "orders.status", "spent_orders_in_yandex.total_price as spent_in_yandex",
                    "oi.product_price as delivery_price"
                )
                ->selectRaw($rawItems)
                ->leftJoin("countries", "countries.id", "=", "orders.country_id")
                ->leftJoin("shops", "shops.id", "=", "orders.shop_id")
                ->leftJoin("users", "users.id", "=", "orders.user_id_operator")
                ->leftJoin("order_items", "order_items.order_id", "=", "orders.id")
                ->leftJoin('order_items AS oi', function ($join) {
                    $join->on('orders.id', '=', 'oi.order_id')
                        ->where('oi.product_name', '=', 'Доставка');
                })
                ->leftJoinSub($spentOrdersInYandex, 'spent_orders_in_yandex', function ($join) {
                    $join->on('spent_orders_in_yandex.order_id', '=', 'orders.id');
                })
                ->filter()
                ->groupBy(
                    "orders.order_number", "orders.created_at", "orders.delivery_mode", "orders.source", "countries.name", "shops.name",
                    "users.name", "orders.client_name", "orders.client_phone", "orders.address", "orders.order_price", "orders.payment_cash",
                    "orders.payment_bonuses", "countries.currency_name", "orders.payment_status", "orders.status", "spent_orders_in_yandex.total_price",
                    "oi.product_price"
                )
                ->orderByDesc('orders.id', 'ASC')
                ->paginate(10)
                ->withQueryString();

            $totalOrderPrice = Order::query()->filter()->sum('order_price');
            $totalPaymentCash = Order::query()->filter()->sum('payment_cash');
            $totalPaymentBonuses = Order::query()->filter()->sum('payment_bonuses');
            $totalDeliveryPrice = Order::query()
                ->leftJoin('order_items AS oi', function ($join) {
                    $join->on('orders.id', '=', 'oi.order_id')
                        ->where('oi.product_name', '=', 'Доставка');
                })
                ->filter()
                ->sum('oi.product_price');
            $totalPriceInYandex = Order::query()
                ->leftJoin("order_delivery_in_yandex", "orders.id", "=", "order_delivery_in_yandex.order_id")
                ->filter()
                ->sum('order_delivery_in_yandex.final_price');
        }
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator')) {
            $available_countries = Country::query()->pluck('id')->all();
        } else {
            $available_countries = Auth::user()->available_countries ?? [];
        }

        $deliveryModes = DeliveryMode::values();
        $sources = OrderSource::values();
        $countries = Country::query()
            ->whereIn('id', $available_countries)
            ->pluck('name', 'id')
            ->all();
        $statuses = OrderStatus::values();
        $paymentStatuses = PaymentStatus::values();
        $operators = User::query()->whereHas('roles', function (Builder $query) {
            $query->where('name', '=', 'operator');
        })->pluck('name', 'id')->toArray();
        $shops = Shop::query()
            ->pluck('name', 'id')
            ->all();

        return view(
            'order_reports.index', compact(
                'orders', 'deliveryModes', 'sources', 'countries', 'statuses', 'paymentStatuses', 'operators', 'shops', 'totalOrderPrice', 'totalPaymentCash', 'totalPaymentBonuses', 'totalDeliveryPrice', 'totalPriceInYandex'
            )
        );
    }

    /**
     * @return BinaryFileResponse
     */
    public function exportToExcel(): BinaryFileResponse
    {
        if(env('DB_CONNECTION') === 'sqlite') {
            $rawItems = "GROUP_CONCAT(order_items.product_sku || ' - ' || order_items.product_name || ' - ' || order_items.product_price || ' - ' || order_items.quantity || ' шт.', '; ') AS items";
        } else { // mysql
            $rawItems = "GROUP_CONCAT(CONCAT(order_items.product_sku, ' - ', order_items.product_name, ' - ', order_items.product_price, ' - ', order_items.quantity, ' шт.') SEPARATOR '; ') AS items";
        }

        $spentOrdersInYandex = Order::query()
            ->selectRaw('SUM(order_delivery_in_yandex.final_price) as total_price, order_delivery_in_yandex.order_id')
            ->leftJoin("order_delivery_in_yandex", "orders.id", "=", "order_delivery_in_yandex.order_id")
            ->groupBy('order_delivery_in_yandex.order_id')
            ->filter();
        
        $orders = Order::query()
            ->select(
                "orders.order_number", "orders.created_at", "orders.delivery_mode", "orders.source", "countries.name AS country_name", "shops.name AS shop_name",
                "users.name AS operator_name", "orders.client_name", "orders.client_phone", "orders.address", "orders.order_price", "orders.payment_cash",
                "orders.payment_bonuses", "countries.currency_name", "orders.payment_status", "orders.status", "spent_orders_in_yandex.total_price as spent_in_yandex",
                "oi.product_price as delivery_price"
            )
            ->selectRaw($rawItems)
            ->leftJoin("countries", "countries.id", "=", "orders.country_id")
            ->leftJoin("shops", "shops.id", "=", "orders.shop_id")
            ->leftJoin("users", "users.id", "=", "orders.user_id_operator")
            ->leftJoin("order_items", "order_items.order_id", "=", "orders.id")
            ->leftJoin('order_items AS oi', function ($join) {
                $join->on('orders.id', '=', 'oi.order_id')
                    ->where('oi.product_name', '=', 'Доставка');
            })
            ->leftJoinSub($spentOrdersInYandex, 'spent_orders_in_yandex', function ($join) {
                $join->on('spent_orders_in_yandex.order_id', '=', 'orders.id');
            })
            ->filter()
            ->groupBy(
                "orders.order_number", "orders.created_at", "orders.delivery_mode", "orders.source", "countries.name", "shops.name",
                "users.name", "orders.client_name", "orders.client_phone", "orders.address", "orders.order_price", "orders.payment_cash",
                "orders.payment_bonuses", "countries.currency_name", "orders.payment_status", "orders.status", "spent_orders_in_yandex.total_price",
                "oi.product_price"
            )
            ->orderByDesc('orders.id', 'ASC')
            ->get()
            ->toArray();

        return Excel::download(new OrderExport($orders), 'orders.xlsx');
    }
}
