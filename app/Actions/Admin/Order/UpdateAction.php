<?php

namespace App\Actions\Admin\Order;

use App\Enums\DeliveryMode;
use App\Enums\OrderStatus;
use Illuminate\Http\RedirectResponse;

class UpdateAction
{
    public function __invoke($request, $order): RedirectResponse
    {
        $data = $request->validated();

        if ($order->delivery_mode === DeliveryMode::OnSpecifiedDate->value) {
            $data['delivery_date'] = $data['delivery_date'] . ' ' . $data['delivery_time'];
        }
        if($data['status'] == OrderStatus::Delivered->value){
            $data['driver_real_date'] = now();
        }

        $order->update($data);

        return redirect()->route('admin.orders.index');
    }
}
