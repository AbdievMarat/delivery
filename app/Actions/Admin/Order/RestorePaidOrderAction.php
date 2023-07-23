<?php

namespace App\Actions\Admin\Order;

use App\Enums\OrderStatus;
use App\Models\OrderLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RestorePaidOrderAction
{
    public function __invoke($order): RedirectResponse
    {
        $orderLog = new OrderLog();
        $orderLog->order_id = $order->id;
        $orderLog->message = 'Заказ был возобновлен';
        $orderLog->user_name = Auth::user()->name;
        $orderLog->user_id = Auth::id();
        $orderLog->save();

        $order->status = OrderStatus::New->value;
        $order->save();

        return redirect()->route('admin.orders.edit', ['order' => $order]);
    }
}
