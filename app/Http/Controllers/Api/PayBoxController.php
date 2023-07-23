<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PayBoxResultRequest;
use App\Models\Order;
use App\Models\OrderLog;
use Illuminate\Http\JsonResponse;

class PayBoxController extends Controller
{
    /**
     * @param PayBoxResultRequest $request
     * @return JsonResponse
     */
    public function result(PayBoxResultRequest $request): JsonResponse
    {
        $order = Order::query()->where('order_number', '=', $request->get('pg_order_id'))->first();

        if($request->get('pg_result') == 1 && $order && $order->payment_status == PaymentStatus::Unpaid->value){
            $order->payment_status = PaymentStatus::Paid->value;
            $order->save();

            $orderLog = new OrderLog();
            $orderLog->order_id = $order->id;
            $orderLog->message = "Платеж PayBox № {$request->get('pg_payment_id')} успешно оплачен";
            $orderLog->user_name = 'PayBox';
            $orderLog->user_id = 1;
            $orderLog->save();

            return response()->json(['orderLog' => $orderLog], 201);
        } else {
            return response()->json([
                'success'   => false,
                'message'   => 'Заказ не оплачен',
                'data'      => []
            ], 404);
        }
    }
}
