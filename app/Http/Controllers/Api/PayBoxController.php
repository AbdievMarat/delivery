<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PayBoxResultRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PayBoxController extends Controller
{
    /**
     * @param PayBoxResultRequest $request
     * @return JsonResponse
     */
    public function result(PayBoxResultRequest $request): JsonResponse
    {
        $order = Order::query()->where('order_number', '=', $request->get('pg_order_id'))->first();

        if ($request->get('pg_result') == 1 && $order) {
            if ($order->payment_status == PaymentStatus::Unpaid->value) {
                $order->payment_status = PaymentStatus::Paid->value;
                $order->save();

                $order->logs()->create([
                    'message' => "Платеж PayBox № {$request->get('pg_payment_id')} успешно оплачен",
                    'user_name' => 'PayBox',
                    'user_id' => User::ADMIN_USER_ID,
                ]);

                $success = true;
                $message = 'Заказ оплачен';
                $status = ResponseAlias::HTTP_OK;
            } else {
                $success = false;
                $message = 'Заказ ранее был оплачен';
                $status = ResponseAlias::HTTP_UNPROCESSABLE_ENTITY;
            }
        } else {
            $success = false;
            $message = 'Заказ не оплачен';
            $status = ResponseAlias::HTTP_INTERNAL_SERVER_ERROR;
        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ], $status);
    }
}
