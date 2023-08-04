@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Лайв заказы</div>
        <div class="card-body overflow-auto">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th style="width: 110px;">№</th>
                    <th style="width: 140px;">Дата</th>
                    <th style="width: 110px;">Доставка</th>
                    <th style="width: 110px;">Источник</th>
                    <th style="width: 110px;">Страна</th>
                    <th style="min-width: 200px;"></th>
                    <th style="width: 75px;">Общее время</th>
                    <th style="width: 200px;">Клиент</th>
                    <th style="width: 165px;">Статусы</th>
                    <th style="width: 100px;"></th>
                </tr>
                </thead>
                @include('admin.orders.tbody-in-orders-table', ['orders' => $orders])
                <tfoot>
                <tr>
                    <th colspan="13">
                        {{ $orders->links() }}
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/admin/orders/index.js', 'resources/js/admin/orders/liveOrders.js'])
    @endpush
@endsection
