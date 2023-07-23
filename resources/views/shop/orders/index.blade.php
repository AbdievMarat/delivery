@extends('layouts.shop')

@section('content')
    <div class="card">
        <div class="card-header">Заказы в работе</div>
        <div class="card-body">
            <form action="{{ route('shop.orders.index') }}" id="search" method="GET"></form>

            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th style="width: 85px;">#</th>
                    <th style="width: 150px;">Магазин</th>
                    <th style="width: 100px;">Дедлайн</th>
                    <th style="width: 150px;">Клиент</th>
                    <th style="width: 250px;">Комментарий</th>
                    <th>Детали</th>
                    <th style="width: 85px;">Выдать</th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->shop->name }}</td>
                        <td></td>
                        <td>{{ $order->client_phone }} <br> {{ $order->client_name }}</td>
                        <td>{{ $order->comment_for_manager }}</td>
                        <td>
                            @foreach($order->items as $item)
                                {{ $item->quantity }} x {{ $item->product_name }} <br>
                            @endforeach
                        </td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <form action="{{ route('shop.transfer_order_to_driver', ['order' => $order]) }}"
                                      method="post">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success transfer-order-to-driver"
                                            title="Выдать продукцию">
                                        <i class="bi bi-check-square"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="7">
                        {{ $orders->links() }}
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/shop/orders/index.js'])
    @endpush
@endsection
