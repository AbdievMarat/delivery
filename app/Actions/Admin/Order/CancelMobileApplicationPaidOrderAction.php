<?php

namespace App\Actions\Admin\Order;

use App\Enums\MobileApplicationBackendOrderPaymentStatus;
use App\Enums\MobileApplicationBackendOrderStatus;
use App\Enums\OrderStatus;
use App\Models\OrderLog;
use App\Services\MobileApplicationBackend;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CancelMobileApplicationPaidOrderAction
{
    /**
     * @param $order
     * @param $request
     * @return JsonResponse
     */
    public function __invoke($order, $request): JsonResponse
    {
        $mobileApplicationBackend = new MobileApplicationBackend($order->country_id);
        $responseMobileApplicationBackend = $mobileApplicationBackend->updateStatus($order->order_number, MobileApplicationBackendOrderStatus::Refund->value, MobileApplicationBackendOrderPaymentStatus::Paid->value);

        if ($responseMobileApplicationBackend->getStatusCode() == ResponseAlias::HTTP_OK) {
            $orderLog = new OrderLog();
            $orderLog->order_id = $order->id;
            $orderLog->message = 'Оплаченный заказ был отменен. Клиенту вернуться денежные средства и начисленные баллы будут вычтены в мобильном приложение.';
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
            $orderLog->message = 'Не удалось отменить оплаченный заказ из мобильного приложения.';
            $orderLog->user_name = Auth::user()->name;
            $orderLog->user_id = Auth::id();
            $orderLog->save();

            return response()->json([]);
        }
    }
}
