<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Events\NewAndProcessedOrderEvent;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $orderLog = new OrderLog();
        $orderLog->order_id = $order->id;
        $orderLog->message = 'Заказ создан';
        $orderLog->user_name = Auth::user()->name ?? '';
        $orderLog->user_id = Auth::id() ?? 1;
        $orderLog->save();

        event(new NewAndProcessedOrderEvent());
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $oldOrderData = $order->getOriginal();
        $changes = $order->getChanges();

        $messages = [];

        if (isset($changes['status'])) {
            if ($changes['status'] == OrderStatus::Delivered->value) {
                $orderLog = new OrderLog();
                $orderLog->order_id = $order->id;
                $orderLog->message = 'Курьер доставил заказ';
                $orderLog->user_name = 'Yandex';
                $orderLog->user_id = 6;
                $orderLog->save();

                event(new NewAndProcessedOrderEvent());
            } else if ($changes['status'] == OrderStatus::Canceled->value) {
                event(new NewAndProcessedOrderEvent());
            } else {
                $messages[] = "Статус заказа был изменен: {$oldOrderData['status']} -> {$changes['status']}";
            }
        }

        if (isset($changes['shop_id'])) {
            if ($oldOrderData['shop_id']) {
                $oldShop = Shop::query()->find($oldOrderData['shop_id']);

                $messages[] = "Магазин был изменен: {$oldShop->name} -> {$order->shop->name}";
            } else {
                $messages[] = "Магазин был выбран: {$order->shop->name}";
            }
        }

        if (isset($changes['address'])) {
            $messages[] = "Адрес был изменен: {$oldOrderData['address']} -> {$changes['address']}";
        }

        if (isset($changes['entrance'])) {
            if ($oldOrderData['entrance']) {
                $messages[] = "Подъезд был изменен: {$oldOrderData['entrance']} -> {$changes['entrance']}";
            } else {
                $messages[] = "Поле 'подъезд' было заполнено: {$changes['entrance']}";
            }
        }

        if (isset($changes['floor'])) {
            if ($oldOrderData['floor']) {
                $messages[] = "Этаж был изменен: {$oldOrderData['floor']} -> {$changes['floor']}";
            } else {
                $messages[] = "Поле 'этаж' было заполнено: {$changes['floor']}";
            }
        }

        if (isset($changes['flat'])) {
            if ($oldOrderData['flat']) {
                $messages[] = "Квартира была изменена: {$oldOrderData['flat']} -> {$changes['flat']}";
            } else {
                $messages[] = "Поле 'квартира' было заполнено: {$changes['flat']}";
            }
        }

        if (isset($changes['comment_for_operator'])) {
            if ($oldOrderData['comment_for_operator']) {
                $messages[] = "Комментарий для оператора был изменен: {$oldOrderData['comment_for_operator']} -> {$changes['comment_for_operator']}";
            } else {
                $messages[] = "Поле 'комментарий для оператора' было заполнено: {$changes['comment_for_operator']}";
            }
        }

        if (isset($changes['comment_for_manager'])) {
            if ($oldOrderData['comment_for_manager']) {
                $messages[] = "Комментарий для магазина был изменен: {$oldOrderData['comment_for_manager']} -> {$changes['comment_for_manager']}";
            } else {
                $messages[] = "Поле 'комментарий для магазина' было заполнено: {$changes['comment_for_manager']}";
            }
        }

        if (isset($changes['comment_for_driver'])) {
            if ($oldOrderData['comment_for_driver']) {
                $messages[] = "Комментарий для курьера был изменен: {$oldOrderData['comment_for_driver']} -> {$changes['comment_for_driver']}";
            } else {
                $messages[] = "Поле 'комментарий для курьера' было заполнено: {$changes['comment_for_driver']}";
            }
        }

        if (isset($changes['delivery_date'])) {
            if (date('Y-m-d H:i', strtotime($oldOrderData['delivery_date'])) !== date('Y-m-d H:i', strtotime($changes['delivery_date']))) {
                $messages[] = "Дата и время доставки была изменена: {$oldOrderData['delivery_date']} -> {$changes['delivery_date']}";
            }
        }

        $messagesData = [];
        foreach ($messages as $message) {
            $messagesData[] = [
                'message' => $message,
                'user_name' => Auth::user()->name ?? '',
                'user_id' => Auth::id() ?? 1,
            ];
        }

        if ($messagesData) {
            $order->logs()->createMany($messagesData);
        }
    }
}
