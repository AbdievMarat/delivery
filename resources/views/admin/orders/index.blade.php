@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Заказы</div>
        <div class="card-body overflow-auto">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <a href="{{ route('admin.orders.create') }}" class="btn btn-success">Создать</a>
                </div>
            </div>

            <form action="{{ route('admin.orders.index') }}" id="search" method="GET"></form>

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
                    <th>Статусы</th>
                    <th style="width: 100px;"></th>
                </tr>
                <tr>
                    <th>
                        <x-input-search type="text" name="order_number" form="search"
                                        value="{{ Request::get('order_number') }}">
                        </x-input-search>
                    </th>
                    <th>
                        <x-input-search type="date" name="created_at" form="search"
                                        value="{{ Request::get('created_at') }}">
                        </x-input-search>
                    </th>
                    <th>
                        <x-select-search name="delivery_mode" form="search" :options="$deliveryModes"
                                         value="{{ Request::get('delivery_mode') }}">
                        </x-select-search>
                    </th>
                    <th>
                        <x-select-search name="source" form="search" :options="$sources"
                                         value="{{ Request::get('source') }}">
                        </x-select-search>
                    </th>
                    <th>
                        <x-select-search name="country_id" form="search" :options="$countries"
                                         value="{{ Request::get('country_id') }}">
                        </x-select-search>
                    </th>
                    <th></th>
                    <th></th>
                    <th>
                        <x-input-search type="text" name="client" form="search" value="{{ Request::get('client') }}">
                        </x-input-search>
                    </th>
                    <th>
                        <x-select-search name="status" form="search" :options="$statuses"
                                         value="{{ Request::get('status') }}">
                        </x-select-search>
                        <x-select-search name="payment_status" form="search" :options="$paymentStatuses"
                                         value="{{ Request::get('payment_status') }}">
                        </x-select-search>
                    </th>
                    <th></th>
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
        @vite(['resources/js/admin/orders/index.js'])
    @endpush
@endsection
