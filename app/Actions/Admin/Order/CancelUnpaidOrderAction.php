<?php

namespace App\Actions\Admin\Order;

use App\Enums\MobileApplicationBackendOrderPaymentStatus;
use App\Enums\MobileApplicationBackendOrderStatus;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Models\OrderLog;
use App\Services\MobileApplicationBackend;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CancelUnpaidOrderAction
{
    public function __invoke($order): RedirectResponse
    {
        if ($order->source == OrderSource::MobileApp->value) {
            $mobileApplicationBackend = new MobileApplicationBackend($order->country_id);
            $responseMobileApplicationBackend = $mobileApplicationBackend->updateStatus($order->order_number, MobileApplicationBackendOrderStatus::CancelUnpaid->value, MobileApplicationBackendOrderPaymentStatus::Unpaid->value);

            if ($responseMobileApplicationBackend->getStatusCode() == ResponseAlias::HTTP_OK) {
                $cancel = true;
            } else {
                $orderLog = new OrderLog();
                $orderLog->order_id = $order->id;
                $orderLog->message = 'Не удалось отменить неоплаченный заказ.';
                $orderLog->user_name = Auth::user()->name;
                $orderLog->user_id = Auth::id();
                $orderLog->save();

                return redirect()->back()->with('error', ['text' => 'Не удалось отменить!']);
            }
        } else {
            $cancel = true;
        }

        if ($cancel) {
            $orderLog = new OrderLog();
            $orderLog->order_id = $order->id;
            $orderLog->message = 'Неоплаченный заказ был отменен.';
            $orderLog->user_name = Auth::user()->name;
            $orderLog->user_id = Auth::id();
            $orderLog->save();

            $order->status = OrderStatus::Canceled->value;
            $order->save();

            return redirect()->back()->with('success', ['text' => 'Успешно отменен!']);
        }
    }
}
