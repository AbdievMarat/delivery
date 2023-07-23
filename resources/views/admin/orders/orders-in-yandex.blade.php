<ol class="list-group mb-3" id="orders-delivery-in-yandex">
    @foreach($ordersInYandex as $order)
        <li class="list-group-item">
            <div>
                <span
                    class="list-group-item list-group-item-action @if($order->status == App\Models\OrderDeliveryInYandex::YANDEX_STATUS_CANCELLED) list-group-item-danger @elseif($order->status == App\Models\OrderDeliveryInYandex::YANDEX_STATUS_DELIVERED_FINISH) list-group-item-success @else list-group-item-info @endif">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1" style="min-width: 170px;">
                            {{ date('d.m.Y H:i', strtotime($order->created_at)) }}
                        </h5>
                        <div class="text-end" style="font-size: 0.875em;">
                            {{ App\Models\OrderDeliveryInYandex::getYandexStatuses()[$order->status] }}<br>
                            @if(in_array($order->status, App\Models\OrderDeliveryInYandex::getYandexStatusesThatCanBeCanceled()))
                                <a class="btn btn-danger float-end btn-sm cancel-order-yandex ms-1"
                                   data-order_delivery_in_yandex_id="{{ $order->id }}"> Отменить
                                </a>
                            @endif
                            @if($order->status == App\Models\OrderDeliveryInYandex::YANDEX_STATUS_READY_FOR_APPROVAL)
                                <a class="btn btn-info float-end btn-sm accept-order-yandex"
                                   data-order_delivery_in_yandex_id="{{ $order->id }}"> Подтвердить
                                </a>
                            @endif
                        </div>
                    </div>
                </span>
                <b>Откуда:</b> {{ $order->shop_address }}<br>
                <b>Куда:</b> {{ $order->client_address }}<br>
                <b>ID:</b> {{ $order->yandex_id }}<br>
                <b>Отправитель:</b> {{ $order->user->name }}<br>
                <b>Тариф:</b> {{ $order->tariff }}<br>
                @if($order->driver_phone && $order->driver_phone_ext)
                    <b>Телефон:</b> {{ $order->driver_phone }}, <b>доб.</b> {{ $order->driver_phone_ext }} <br>
                @endif
                @if($order->offer_price > 0)
                    <b>Предварительная стоимость:</b>
                    <span
                        class="badge rounded-pill fs-6 @if($order->offer_price > $deliveryPrice) bg-danger @else bg-success @endif">
                        <i class="bi @if($order->offer_price > $deliveryPrice) bi-exclamation-circle @else bi-check2 @endif"></i>
                        {{ $order->offer_price }}
                    </span>
                    <br>
                @endif
                @if($order->final_price > 0)
                    <b>Итоговая стоимость:</b>
                    <span class="badge bg-primary rounded-pill fs-6"> <i class="bi bi-cash"></i> {{ $order->final_price }}</span>
                @endif
            </div>
        </li>
    @endforeach
</ol>
