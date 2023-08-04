<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * @param Request $request
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(Request $request): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $attachedShops = Auth::user()->attached_shops ?? [];

        $orders = Order::with('shop', 'items')
            ->select("orders.*")
            ->where('status', '=', OrderStatus::InShop)
            ->whereIn('shop_id', $attachedShops)
            ->filter($request->all())
            ->orderByDesc('orders.id')
            ->paginate(10)
            ->withQueryString();

        return view('shop.orders.index', compact('orders'));
    }

    /**
     * @param Order $order
     * @return RedirectResponse
     */
    public function transferOrderToDriver(Order $order): RedirectResponse
    {
        if($order->status == OrderStatus::InShop->value) {
            $order->transferOrderToDriver();

            $order->logs()->create([
                'message' => 'Продукция выдана курьеру',
                'user_name' => Auth::user()->name,
                'user_id' => Auth::id(),
            ]);
        }

        return redirect()->route('shop.orders.index')->with('success', ['text' => 'Успешно сохранено!']);
    }
}
