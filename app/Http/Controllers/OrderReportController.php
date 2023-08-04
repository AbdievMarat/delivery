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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderReportController extends Controller
{
    /**
     * @param Request $request
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(Request $request): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $dateFrom = $request->get('date_from') ?? date('Y-m-d', strtotime("-1 month"));
        $dateTo = $request->get('date_to') ?? date("Y-m-d");

        if (!empty($request->all())) {
            if (env('DB_CONNECTION') === 'sqlite') {
                $rawItems = "GROUP_CONCAT(order_items.product_sku || ' - ' || order_items.product_name || ' - ' || order_items.product_price || ' - ' || order_items.quantity || ' шт.', '; ') AS items";
            } else { // mysql
                $rawItems = "GROUP_CONCAT(CONCAT(order_items.product_sku, ' - ', order_items.product_name, ' - ', order_items.product_price, ' - ', order_items.quantity, ' шт.') SEPARATOR '; ') AS items";
            }

            $data = $request->all();
            $data['date_from'] = $dateFrom;
            $data['date_to'] = $dateTo;

            if($request->has('yandex_id')) {
                $order_id = OrderDeliveryInYandex::query()
                    ->where('yandex_id', '=', $request->get('yandex_id'))
                    ->pluck('order_id')
                    ->first();
                $data['id'] = $order_id;
            }
            $spentOrdersInYandex = Order::query()
                ->selectRaw('SUM(order_delivery_in_yandex.final_price) as total_price, order_delivery_in_yandex.order_id')
                ->leftJoin("order_delivery_in_yandex", "orders.id", "=", "order_delivery_in_yandex.order_id")
                ->groupBy('order_delivery_in_yandex.order_id')
                ->filter($data);

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
                ->filter($data)
                ->groupBy(
                    "orders.order_number", "orders.created_at", "orders.delivery_mode", "orders.source", "countries.name", "shops.name",
                    "users.name", "orders.client_name", "orders.client_phone", "orders.address", "orders.order_price", "orders.payment_cash",
                    "orders.payment_bonuses", "countries.currency_name", "orders.payment_status", "orders.status", "spent_orders_in_yandex.total_price",
                    "oi.product_price"
                )
                ->orderByDesc('orders.id', 'ASC')
                ->paginate(10)
                ->withQueryString();

            $totalOrderPayment = Order::query()
                ->filter($data)
                ->selectRaw('SUM(order_price) as total_order_price, SUM(payment_cash) as total_payment_cash, SUM(payment_bonuses) as total_payment_bonuses')
                ->first();

            $totalDeliveryPrice = Order::query()
                ->leftJoin('order_items AS oi', function ($join) {
                    $join->on('orders.id', '=', 'oi.order_id')
                        ->where('oi.product_name', '=', 'Доставка');
                })
                ->filter($data)
                ->sum('oi.product_price');
            $totalPriceInYandex = Order::query()
                ->leftJoin("order_delivery_in_yandex", "orders.id", "=", "order_delivery_in_yandex.order_id")
                ->filter($data)
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

        return view('order_reports.index', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'deliveryModes' => $deliveryModes,
            'sources' => $sources,
            'countries' => $countries,
            'statuses' => $statuses,
            'paymentStatuses' => $paymentStatuses,
            'operators' => $operators,
            'shops' => $shops,
            'orders' => $orders ?? [],
            'totalOrderPrice' => $totalOrderPayment->total_order_price ?? 0,
            'totalPaymentCash' => $totalOrderPayment->total_payment_cash ?? 0,
            'totalPaymentBonuses' => $totalOrderPayment->total_payment_bonuses ?? 0,
            'totalDeliveryPrice' => $totalDeliveryPrice ?? 0,
            'totalPriceInYandex' => $totalPriceInYandex ?? 0,
        ]);
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportToExcel(Request $request): BinaryFileResponse
    {
        if (env('DB_CONNECTION') === 'sqlite') {
            $rawItems = "GROUP_CONCAT(order_items.product_sku || ' - ' || order_items.product_name || ' - ' || order_items.product_price || ' - ' || order_items.quantity || ' шт.', '; ') AS items";
        } else { // mysql
            $rawItems = "GROUP_CONCAT(CONCAT(order_items.product_sku, ' - ', order_items.product_name, ' - ', order_items.product_price, ' - ', order_items.quantity, ' шт.') SEPARATOR '; ') AS items";
        }

        $data = $request->all();

        if($request->has('yandex_id')) {
            $order_id = OrderDeliveryInYandex::query()
                ->where('yandex_id', '=', $request->get('yandex_id'))
                ->pluck('order_id')
                ->first();
            $data['id'] = $order_id;
        }

        $spentOrdersInYandex = Order::query()
            ->selectRaw('SUM(order_delivery_in_yandex.final_price) as total_price, order_delivery_in_yandex.order_id')
            ->leftJoin("order_delivery_in_yandex", "orders.id", "=", "order_delivery_in_yandex.order_id")
            ->groupBy('order_delivery_in_yandex.order_id')
            ->filter($data);

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
            ->filter($data)
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
