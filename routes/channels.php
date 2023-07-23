<?php

use App\Broadcasting\OrderInYandexChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('ordersInYandex.order.{orderId}', OrderInYandexChannel::class);
