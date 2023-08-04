<tbody class="table-group-divider">
@foreach($orders as $order)
    <tr>
        <td>{{ $order->order_number }}</td>
        <td>{{ date('d.m.Y H:i', strtotime($order->created_at)) }}</td>
        <td>
            @if($order->delivery_mode == App\Enums\DeliveryMode::SoonAsPossible->value)
                {{ $order->delivery_mode }}
            @else
                <span class="badge fs-6 @if($order->delivery_date >= date('Y-m-d')) bg-primary @else bg-danger @endif">
                    {{ date('d.m.Y H:i', strtotime($order->delivery_date)) }}
                </span>
            @endif

        </td>
        <td>{{ $order->source }}</td>
        <td>{{ $order->country_name }}</td>
        <td>
            @if($order->address)
                <span class="text-primary mb-2">
                    <i class="bi bi-map"></i> {{ $order->address }}
                </span><br>
            @endif
            @if(($order->operator && $order->operator->name))
                <b>Оператор:</b> {{ $order->operator->name }} <br>
            @endif
            @if($order->comment_for_operator)
                <span class="text-primary mb-2">
                    <i class="bi bi-chat-dots"></i> {{ $order->comment_for_operator }}
                </span><br>
            @endif
            @if($order->shop && $order->shop->name)
                <b>Магазин:</b> {{ $order->shop->name }} <br>
            @endif
            @if($order->comment_for_driver)
                <span class="text-primary mb-2">
                <i class="bi bi-star-fill"></i> {{ $order->comment_for_driver }}
            </span><br>
            @endif
            @if($order->deliveryInYandex)
                @foreach($order->deliveryInYandex as $deliveryInYandex)
                    <span class="text-danger d-block mb-2">
                        <i class="bi bi-taxi-front-fill"></i> {{ App\Models\OrderDeliveryInYandex::getYandexStatuses()[$deliveryInYandex->status] }}
                    </span>
                @endforeach
            @endif
        </td>
        <td>{{ $order->totalProcessingTime ?? '' }}</td>
        <td>
            <span class="badge bg-info fs-6">
                <i class="bi bi-person"></i> {{ $order->client_phone }} <br>
                <i class="bi bi-telephone"></i> {{ $order->client_name }}
            </span>
        </td>
        <td>
            <span class="badge fs-6 {{ App\Enums\OrderStatus::from($order->status)->colorClass() }}">
                <i class="bi bi-info-circle"></i> {{ $order->status }}
            </span>
            <span class="badge fs-6 mt-2 {{ App\Enums\PaymentStatus::from($order->payment_status)->colorClass() }}">
                <i class="bi bi-credit-card-fill"></i> {{ $order->payment_status }}
            </span>
        </td>
        <td>
            <div class="d-flex">
                <div class="mb-2">
                    <a href="{{ route('admin.orders.show', ['order' => $order]) }}"
                       type="button"
                       class="btn btn-success"><i class="bi bi-eye"></i>
                    </a>
                </div>
                @can('update', $order)
                    <div class="mb-2 ms-2">
                        <a href="{{ route('admin.orders.edit', ['order' => $order]) }}"
                           type="button"
                           class="btn btn-warning"><i class="bi bi-pencil-square"></i>
                        </a>
                    </div>
                @endcan
                @can('cancelUnpaid', $order)
                    <form action="{{ route('admin.cancel_unpaid_order', ['order' => $order]) }}"
                          method="post"
                          class="ms-2">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                class="btn btn-danger cancel-unpaid-order"
                                title="Отменить неоплаченный заказ"><i class="bi bi-x-lg"></i>
                        </button>
                    </form>
                @endcan
            </div>
        </td>
    </tr>
@endforeach
</tbody>
