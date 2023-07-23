<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IssuedOrdersController extends Controller
{
    public function index(Request $request): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $managerRealDateFrom = $request->get('manager_real_date_from') ?? date('Y-m-d');
        $managerRealDateTo = $request->get('manager_real_date_to') ?? date("Y-m-d", strtotime("+1 day"));

        $attachedShops = Auth::user()->attached_shops ?? [];

        $orders = Order::with('items')
            ->select("orders.*")
            ->where('status', '=', OrderStatus::Delivered)
            ->whereNotNull('manager_real_date')
            ->whereIn('shop_id', $attachedShops)
            ->filter($managerRealDateFrom, $managerRealDateTo)
            ->paginate(10)
            ->withQueryString();

        return view('shop.issued_orders.index', compact('orders', 'managerRealDateFrom', 'managerRealDateTo'));
    }
}
