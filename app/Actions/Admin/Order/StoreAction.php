<?php

namespace App\Actions\Admin\Order;

use App\Enums\DeliveryMode;
use App\Models\Order;
use App\Models\OrderLog;
use App\Services\PayBox;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StoreAction
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __invoke($request): RedirectResponse
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

        $orderLog = new OrderLog();
        $orderLog->order_id = $order->id;
        $orderLog->message = "Платеж в PayBox был сформирован с № {$payBoxResponse['pg_payment_id']}";
        $orderLog->user_name = Auth::user()->name;
        $orderLog->user_id = Auth::id();
        $orderLog->save();

        $order->payment_url = $payBoxResponse['pg_redirect_url'];
        $order->save();

        return redirect()->route('admin.orders.index')->with('success', ['text' => 'Успешно добавлено!']);
    }
}
