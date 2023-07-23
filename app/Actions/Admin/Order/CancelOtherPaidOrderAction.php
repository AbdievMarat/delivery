<?php

namespace App\Actions\Admin\Order;

use App\Enums\OrderStatus;
use App\Models\OrderLog;
use App\Services\PayBox;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CancelOtherPaidOrderAction
{
    /**
     * @param $order
     * @param $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function __invoke($order, $request): JsonResponse
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
            $orderLog = new OrderLog();
            $orderLog->order_id = $order->id;
            $orderLog->message = 'Оплаченный прочий заказ был отменен. Клиенту вернуться денежные средства.';
            $orderLog->user_name = Auth::user()->name;
            $orderLog->user_id = Auth::id();
            $orderLog->save();

            $reasonCancel = $request->get('reason_cancel');

            $commentForOperator = trim($order->comment_for_operator); // удаление пробелов из начала и конца строки

            if (strlen($commentForOperator) === 0) { // если комментария не было
                $commentForOperator .= '';
            } else if (str_ends_with($commentForOperator, '.')) { // если есть точка в конце строки, то добавляем пробел
                $commentForOperator .= ' ';
            } else {
                $commentForOperator .= '. '; // иначе добавляем точки в конце строки
            }

            $commentForOperator .= 'Причина отмены: ' . $reasonCancel;

            $order->comment_for_operator = $commentForOperator;
            $order->status = OrderStatus::Canceled->value;
            $order->save();

            return response()->json([]);
        } else {
            $orderLog = new OrderLog();
            $orderLog->order_id = $order->id;
            $orderLog->message = 'Не удалось отменить оплаченный прочий заказ.';
            $orderLog->user_name = Auth::user()->name;
            $orderLog->user_id = Auth::id();
            $orderLog->save();

            return response()->json([]);
        }
    }
}
