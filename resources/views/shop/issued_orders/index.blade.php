@extends('layouts.shop')

@section('content')
    <div class="card">
        <div class="card-header">Выданные заказы</div>
        <div class="card-body">
            <form action="{{ route('shop.issued_orders') }}" id="search" method="GET"></form>

            <table class="table table-bordered table-hover">
                <thead>
                <tr class="text-center">
                    <th style="width: 100px;">№</th>
                    <th style="width: 100px;">Дата заказа</th>
                    <th style="width: 150px;" colspan="2">Дата выдачи</th>
                    <th style="width: 150px;">Клиент</th>
                    <th>Детали</th>
                </tr>
                <tr>
                    <th>
                        <x-input-search type="text" name="id" form="search" value="{{ Request::get('order_number') }}">
                        </x-input-search>
                    </th>
                    <th></th>
                    <th>
                        <x-input-search type="date" name="manager_real_date_from" form="search" value="{{ $managerRealDateFrom }}">
                        </x-input-search>
                    </th>
                    <th>
                        <x-input-search type="date" name="manager_real_date_to" form="search" value="{{ $managerRealDateTo }}">
                        </x-input-search>
                    </th>
                    <th>
                        <x-input-search type="text" name="client" form="search" value="{{ Request::get('client') }}">
                        </x-input-search>
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ date('d.m.Y H:i', strtotime($order->created_at)) }}</td>
                        <td colspan="2">{{ date('d.m.Y H:i', strtotime($order->manager_real_date)) }}</td>
                        <td>{{ $order->client_phone }} <br> {{ $order->client_name }}</td>
                        <td>
                            @foreach($order->items as $item)
                                {{ $item->quantity }} x {{ $item->product_name }} <br>
                            @endforeach
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
@endsection
