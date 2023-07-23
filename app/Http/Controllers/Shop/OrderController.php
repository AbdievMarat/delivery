<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLog;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $attachedShops = Auth::user()->attached_shops ?? [];

        $orders = Order::with('shop', 'items')
            ->select("orders.*")
            ->where('status', '=', OrderStatus::InShop)
            ->whereIn('shop_id', $attachedShops)
            ->filter()
            ->orderByDesc('orders.id')
            ->paginate(10)
            ->withQueryString();

        return view('shop.orders.index', compact('orders'));
    }

    public function transferOrderToDriver(Order $order): RedirectResponse
    {
        if($order->status == OrderStatus::InShop->value) {
            $order->transferOrderToDriver();

            $orderLog = new OrderLog();
            $orderLog->order_id = $order->id;
            $orderLog->message = 'Продукция выдана курьеру';
            $orderLog->user_name = Auth::user()->name ?? '';
            $orderLog->user_id = Auth::id();
            $orderLog->save();
        }

        return redirect()->route('shop.orders.index')->with('success', ['text' => 'Успешно сохранено!']);
    }
}
