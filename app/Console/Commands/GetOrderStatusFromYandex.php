<?php

namespace App\Console\Commands;

use App\Enums\MobileApplicationBackendOrderPaymentStatus;
use App\Enums\MobileApplicationBackendOrderStatus;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderDeliveryInYandex;
use App\Models\OrderLog;
use App\Services\DeliveryYandex;
use App\Services\MobileApplicationBackend;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GetOrderStatusFromYandex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-order-status-from-yandex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @return void
     * @throws GuzzleException
     */
    public function handle(): void
    {
        // обновляем статусы
        $yandexOrders = OrderDeliveryInYandex::query()
            ->select('order_delivery_in_yandex.id', 'order_delivery_in_yandex.yandex_id', 'order_delivery_in_yandex.order_id', 'order_delivery_in_yandex.status', 'orders.country_id')
            ->join('orders', 'order_delivery_in_yandex.order_id', '=', 'orders.id')
            ->whereNotIn('order_delivery_in_yandex.status', [
                OrderDeliveryInYandex::YANDEX_STATUS_CANCELLED,
                OrderDeliveryInYandex::YANDEX_STATUS_DELIVERED_FINISH,
                OrderDeliveryInYandex::YANDEX_STATUS_CANCELLED_WITH_PAYMENT,
                OrderDeliveryInYandex::YANDEX_STATUS_RETURNED_FINISH
            ])
            ->whereIn('orders.status', [
                OrderStatus::InShop,
                OrderStatus::AtDriver
            ])
            ->get()
            ->toArray();

        foreach ($yandexOrders as $yandexOrder) {
            $yandex = new DeliveryYandex($yandexOrder['country_id']);
            $responseYandex = $yandex->getOrderYandexInfo($yandexOrder['yandex_id']);

            if ($responseYandex->getStatusCode() == ResponseAlias::HTTP_OK) {
                $responseYandexData = json_decode($responseYandex->getContent(), true);

                if ($yandexOrder['status'] != $responseYandexData['status']) {
                    $orderDeliveryInYandex = OrderDeliveryInYandex::findOrFail($yandexOrder['id']);
                    $orderDeliveryInYandex->status = $responseYandexData['status'];
                    $orderDeliveryInYandex->offer_price = $responseYandexData['pricing']['offer']['price'];
                    $orderDeliveryInYandex->update();

                    if (
                        ($responseYandexData['status'] == OrderDeliveryInYandex::YANDEX_STATUS_DELIVERED_FINISH || $responseYandexData['status'] == OrderDeliveryInYandex::YANDEX_STATUS_RETURNED_FINISH) &&
                        $responseYandexData['pricing']['final_price']
                    ) {
                        $orderDeliveryInYandex->final_price = $responseYandexData['pricing']['final_price'];
                        $orderDeliveryInYandex->update();
                    }
                }
            }
        }

        // закрываем доставленные заказы
        $atDriverOrders = OrderDeliveryInYandex::query()
            ->select('order_delivery_in_yandex.order_id')
            ->join('orders', 'order_delivery_in_yandex.order_id', '=', 'orders.id')
            ->whereIn('order_delivery_in_yandex.status', [
                OrderDeliveryInYandex::YANDEX_STATUS_DELIVERED_FINISH,
            ])
            ->whereIn('orders.status', [
                OrderStatus::AtDriver
            ])
            ->get()
            ->toArray();

        foreach ($atDriverOrders as $yandexOrder) {
            $order = Order::findOrFail($yandexOrder['order_id']);
            $order->orderDelivered();

            if ($order->source == OrderSource::MobileApp->value) {
                $mobileApplicationBackend = new MobileApplicationBackend($order->country_id);
                $responseMobileApplicationBackend = $mobileApplicationBackend->updateStatus($order->order_number, MobileApplicationBackendOrderStatus::Delivered->value, MobileApplicationBackendOrderPaymentStatus::Paid->value);

                if ($responseMobileApplicationBackend->getStatusCode() == ResponseAlias::HTTP_OK) {
                    $message = 'Оплаченный заказ завершен в МП.';
                } else {
                    $message = 'Не удалось завершить оплаченный заказ в МП.';
                }

                $orderLog = new OrderLog();
                $orderLog->order_id = $order->id;
                $orderLog->message = $message;
                $orderLog->user_name = 'Сервер мобильного приложения';
                $orderLog->user_id = 3;
                $orderLog->save();
            }
        }

        // получаем номера телефонов курьеров
        $driverOrders = OrderDeliveryInYandex::query()
            ->select('order_delivery_in_yandex.id', 'order_delivery_in_yandex.yandex_id', 'orders.country_id')
            ->join('orders', 'order_delivery_in_yandex.order_id', '=', 'orders.id')
            ->whereIn('order_delivery_in_yandex.status', [
                OrderDeliveryInYandex::YANDEX_STATUS_DELIVERED,
                OrderDeliveryInYandex::YANDEX_STATUS_DELIVERY_ARRIVED,
                OrderDeliveryInYandex::YANDEX_STATUS_PERFORMER_FOUND,
                OrderDeliveryInYandex::YANDEX_STATUS_PICKUP_ARRIVED,
                OrderDeliveryInYandex::YANDEX_STATUS_PICKUPED,
            ])
            ->whereIn('orders.status', [
                OrderStatus::InShop,
                OrderStatus::AtDriver
            ])
            ->whereNull('order_delivery_in_yandex.driver_phone')
            ->get()
            ->toArray();

        foreach ($driverOrders as $yandexOrder) {
            $yandex = new DeliveryYandex($yandexOrder['country_id']);
            $responseYandex = $yandex->getDriverPhoneYandex($yandexOrder['yandex_id']);

            if ($responseYandex->getStatusCode() == ResponseAlias::HTTP_OK) {
                $responseYandexData = json_decode($responseYandex->getContent(), true);

                if ($responseYandexData['phone'] && $responseYandexData['ext']) {
                    $orderDeliveryInYandex = OrderDeliveryInYandex::findOrFail($yandexOrder['id']);
                    $orderDeliveryInYandex->driver_phone = $responseYandexData['phone'];
                    $orderDeliveryInYandex->driver_phone_ext = $responseYandexData['ext'];
                    $orderDeliveryInYandex->update();
                }
            }
        }
    }
}
