<?php

namespace App\Policies;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function update(User $user, Order $order): bool
    {
        return
            !in_array($order->status, [OrderStatus::Canceled->value, OrderStatus::Delivered->value]) &&
            $order->payment_status == PaymentStatus::Paid->value;
    }

    /**
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function cancelUnpaid(User $user, Order $order): bool
    {
        return
            $order->status == OrderStatus::New->value &&
            $order->payment_status == PaymentStatus::Unpaid->value &&
            (
                $order->source == OrderSource::MobileApp->value ||
                (
                    $order->source == OrderSource::Other->value &&
                    $order->payment_url &&
                    date("Y-m-d H:i:s") > date('Y-m-d H:i:s', strtotime($order->created_at . ' +1 hour'))
                )
            );
    }

    /**
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function restorePaid(User $user, Order $order): bool
    {
        return
            $order->status == OrderStatus::Delivered->value &&
            $order->payment_status == PaymentStatus::Paid->value &&
            date("Y-m-d H:i:s") < date('Y-m-d H:i:s', strtotime($order->updated_at . ' +24 hour'));
    }

    /**
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function cancelMobileApplicationPaid(User $user, Order $order): bool
    {
        return
            $order->status == OrderStatus::New->value &&
            $order->payment_status == PaymentStatus::Paid->value &&
            $order->source == OrderSource::MobileApp->value;
    }

    public function cancelOtherPaid(User $user, Order $order): bool
    {
        return
            $order->status == OrderStatus::New->value &&
            $order->payment_status == PaymentStatus::Paid->value &&
            $order->source == OrderSource::Other->value;
    }

    /**
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function copyPaymentUrl(User $user, Order $order): bool
    {
        return
            $order->payment_url &&
            $order->payment_status === PaymentStatus::Unpaid->value &&
            date("Y-m-d H:i:s") < date('Y-m-d H:i:s', strtotime($order->created_at . ' +1 hour'));
    }
}
