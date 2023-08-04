<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryMode;
use App\Enums\MobileApplicationBackendOrderPaymentStatus;
use App\Enums\MobileApplicationBackendOrderStatus;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShopStatus;
use App\Exceptions\UnsupportedCountryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderDeliveryInYandex;
use App\Models\Shop;
use App\Services\MobileApplicationBackend;
use App\Services\PayBox;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:update,order')->only('edit', 'update');
        $this->middleware('can:cancelUnpaid,order')->only('cancelUnpaidOrder');
        $this->middleware('can:restorePaid,order')->only('restorePaidOrder');
        $this->middleware('can:cancelMobileApplicationPaid,order')->only('cancelMobileApplicationPaidOrder');
        $this->middleware('can:cancelOtherPaid,order')->only('cancelOtherPaidOrder');
    }

    /**
     * @param Request $request
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(Request $request): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $orders = Order::with([
            'deliveryInYandex' => function ($query) {
                $query->where('status', '!=', OrderDeliveryInYandex::YANDEX_STATUS_CANCELLED);
            },
            'shop', 'operator'])
            ->select("orders.*", "countries.name AS country_name")
            ->join("countries", "countries.id", "=", "orders.country_id")
            ->filter($request->all())
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

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function create(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $countries = Country::query()->pluck('name', 'id')->all();
        $deliveryModes = DeliveryMode::values();
        $sources = [OrderSource::Other->value => OrderSource::Other->value];

        return view('admin.orders.create', compact('sources', 'deliveryModes', 'countries'));
    }

    /**
     * @param StoreOrderRequest $request
     * @return RedirectResponse
     * @throws GuzzleException
     * @throws UnsupportedCountryException
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $orderNumber = Order::generateOrderNumber();

        $data = $request->validated();
        $data['payment_cash'] = $data['order_price'];
        $data['order_number'] = $orderNumber;
        if ($data['delivery_mode'] == DeliveryMode::OnSpecifiedDate->value) {
            $data['delivery_date'] = $data['delivery_date'] . ' ' . $data['delivery_time'];
        }

        $order = new Order($data);
        $order->save();

        $items = [];
        foreach ($request->get('product_sku') as $key => $sku) {
            if ($sku) {
                $items[] = [
                    'product_name' => $request->get('product_name')[$key],
                    'product_sku' => $sku,
                    'product_price' => $request->get('product_price')[$key],
                    'quantity' => $request->get('quantity')[$key],
                ];
            }
        }

        if ($items) {
            $order->items()->createMany($items); // Создаем связанные модели массово
        }

        $description = "Заказ № {$order->id}";

        $payBox = new PayBox($order->country_id);
        $payBoxResponse = $payBox->initiatePayment($order->order_number, $order->payment_cash, $description, $order->client_phone, $order->items);

        $order->logs()->create([
            'message' => "Платеж в PayBox был сформирован с № {$payBoxResponse['pg_payment_id']}",
            'user_name' => Auth::user()->name,
            'user_id' => Auth::id(),
        ]);

        $order->payment_url = $payBoxResponse['pg_redirect_url'];
        $order->save();

        return redirect()->route('admin.orders.index')->with('success', ['text' => 'Успешно добавлено!']);
    }

    /**
     * @param Order $order
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function show(Order $order): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $order->load('deliveryInYandex.user');

        $deliveryPrice = $order
            ->items()
            ->where('product_name', '=', 'Доставка')
            ->pluck('product_price')
            ->first();
        $count_of_orders_to_yandex_awaiting_estimate = $order
            ->deliveryInYandex()
            ->where('status', '!=', OrderDeliveryInYandex::YANDEX_STATUS_READY_FOR_APPROVAL)
            ->where('offer_price', '=', '0')
            ->count();

        return view('admin.orders.show', compact('order', 'deliveryPrice', 'count_of_orders_to_yandex_awaiting_estimate'));
    }

    /**
     * @param Order $order
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function edit(Order $order): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $order->load('deliveryInYandex.user');

        $deliveryModes = DeliveryMode::values();
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
        $deliveryPrice = $order
            ->items()
            ->where('product_name', '=', 'Доставка')
            ->pluck('product_price')
            ->first();
        $count_of_orders_to_yandex_awaiting_estimate = $order
            ->deliveryInYandex()
            ->where('status', '!=', OrderDeliveryInYandex::YANDEX_STATUS_READY_FOR_APPROVAL)
            ->where('offer_price', '=', '0')
            ->count();

        return view('admin.orders.edit', compact('order', 'deliveryModes', 'statuses', 'shops', 'deliveryPrice', 'count_of_orders_to_yandex_awaiting_estimate'));
    }

    /**
     * @param UpdateOrderRequest $request
     * @param Order $order
     * @return RedirectResponse
     */
    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        if ($data['delivery_mode'] === DeliveryMode::OnSpecifiedDate->value) {
            $deliveryDate = $data['delivery_date'] . ' ' . $data['delivery_time'];
        }
        $data['delivery_date'] = $deliveryDate ?? null;

        if($data['status'] == OrderStatus::Delivered->value){
            $data['driver_real_date'] = now();
        }

        $order->update($data);

        return redirect()->route('admin.orders.index');
    }

    /**
     * @param Request $request
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function liveOrders(Request $request): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $orders = Order::with([
            'deliveryInYandex' => function ($query) {
                $query->where('status', '!=', OrderDeliveryInYandex::YANDEX_STATUS_CANCELLED);
            },
            'shop', 'operator'])
            ->select("orders.*", "countries.name AS country_name")
            ->join("countries", "countries.id", "=", "orders.country_id")
            ->filter($request->all())
            ->whereNotIn('orders.status', [OrderStatus::Delivered->value, OrderStatus::Canceled->value])
            ->orderBy('orders.payment_status', 'ASC')
            ->orderByDesc('orders.id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.live', compact('orders'));
    }

    /**
     * @param Order $order
     * @return RedirectResponse
     * @throws UnsupportedCountryException
     */
    public function cancelUnpaidOrder(Order $order): RedirectResponse
    {
        $cancel = false;

        if ($order->source == OrderSource::MobileApp->value) {
            $mobileApplicationBackend = new MobileApplicationBackend($order->country_id);
            $responseMobileApplicationBackend = $mobileApplicationBackend->updateStatus($order->order_number, MobileApplicationBackendOrderStatus::CancelUnpaid->value, MobileApplicationBackendOrderPaymentStatus::Unpaid->value);

            if ($responseMobileApplicationBackend->getStatusCode() == ResponseAlias::HTTP_OK) {
                $cancel = true;
            } else {
                $order->logs()->create([
                    'message' => 'Не удалось отменить неоплаченный заказ.',
                    'user_name' => Auth::user()->name,
                    'user_id' => Auth::id(),
                ]);
            }
        } else {
            $cancel = true;
        }

        if ($cancel) {
            $order->logs()->create([
                'message' => 'Неоплаченный заказ был отменен.',
                'user_name' => Auth::user()->name,
                'user_id' => Auth::id(),
            ]);

            $order->status = OrderStatus::Canceled->value;
            $order->save();

            return redirect()->back()->with('success', ['text' => 'Успешно отменен!']);
        } else {
            return redirect()->back()->with('error', ['text' => 'Не удалось отменить!']);
        }
    }

    /**
     * @param Order $order
     * @param Request $request
     * @return JsonResponse
     * @throws UnsupportedCountryException
     */
    public function cancelMobileApplicationPaidOrder(Order $order, Request $request): JsonResponse
    {
        $mobileApplicationBackend = new MobileApplicationBackend($order->country_id);
        $responseMobileApplicationBackend = $mobileApplicationBackend->updateStatus($order->order_number, MobileApplicationBackendOrderStatus::Refund->value, MobileApplicationBackendOrderPaymentStatus::Paid->value);

        if ($responseMobileApplicationBackend->getStatusCode() == ResponseAlias::HTTP_OK) {
            $order->logs()->create([
                'message' => 'Оплаченный заказ был отменен. Клиенту вернуться денежные средства и начисленные баллы будут вычтены в мобильном приложение.',
                'user_name' => Auth::user()->name,
                'user_id' => Auth::id(),
            ]);

            $order->statusChangeToCanceledForPaidOrder($request->get('reason_cancel'));

            return response()->json();
        } else {
            $message = 'Не удалось отменить оплаченный заказ из мобильного приложения.';

            $order->logs()->create([
                'message' => $message,
                'user_name' => Auth::user()->name,
                'user_id' => Auth::id(),
            ]);

            return response()->json(['text' => $message], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Order $order
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     * @throws UnsupportedCountryException
     */
    public function cancelOtherPaidOrder(Order $order, Request $request): JsonResponse
    {
        $payBoxLog = $order
            ->logs()
            ->where('message', 'LIKE', '%Платеж в PayBox был сформирован с №%')
            ->pluck('message')
            ->first();

        $payBoxId = preg_replace('/\D/', '', $payBoxLog);

        $payBox = new PayBox($order->country_id);
        $payBoxResponse = $payBox->refundPayment($payBoxId, $order->payment_cash);

        if ($payBoxResponse['pg_status'] == 'ok') {
            $order->logs()->create([
                'message' => 'Оплаченный прочий заказ был отменен. Клиенту вернуться денежные средства.',
                'user_name' => Auth::user()->name,
                'user_id' => Auth::id(),
            ]);

            $order->statusChangeToCanceledForPaidOrder($request->get('reason_cancel'));

            return response()->json();
        } else {
            $message = 'Не удалось отменить оплаченный прочий заказ.';

            $order->logs()->create([
                'message' => $message,
                'user_name' => Auth::user()->name,
                'user_id' => Auth::id(),
            ]);

            return response()->json(['text' => $message], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Order $order
     * @return RedirectResponse
     */
    public function restorePaidOrder(Order $order): RedirectResponse
    {
        $order->logs()->create([
            'message' => 'Заказ был возобновлен',
            'user_name' => Auth::user()->name,
            'user_id' => Auth::id(),
        ]);

        $order->status = OrderStatus::New->value;
        $order->save();

        return redirect()->route('admin.orders.edit', ['order' => $order]);
    }
}
