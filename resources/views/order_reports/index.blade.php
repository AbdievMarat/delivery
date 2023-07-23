@extends(Auth::user()->hasRole('accountant') ? 'layouts.accountant' : 'layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Отчёт по заказам</div>
        <div class="card-body overflow-auto">

            <form action="{{ route('order_report') }}" id="reportOrders" method="GET"></form>
            <form action="{{ route('order_report_export_to_excel') }}" id="orderReportExportToExcel"
                  method="GET"></form>

            <div class="panel panel-white panel-body">
                <div class="row">
                    <div class="col-md-1">
                        <input type="text" form="reportOrders" placeholder="Заказ №" title="Заказ №" name="id"
                               autocomplete="off"
                               value="{{ Request::get('id') }}" class="form-control">
                        <input type="hidden" form="orderReportExportToExcel" name="id" value="{{ Request::get('id') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" form="reportOrders" placeholder="Дата от" title="Дата от" name="date_from"
                               autocomplete="off"
                               value="{{ Request::get('date_from') }}" class="form-control">
                        <input type="hidden" form="orderReportExportToExcel" name="date_from"
                               value="{{ Request::get('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" form="reportOrders" placeholder="Дата до" title="Дата до" name="date_to"
                               autocomplete="off"
                               value="{{ Request::get('date_to') }}" class="form-control">
                        <input type="hidden" form="orderReportExportToExcel" name="date_to"
                               value="{{ Request::get('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="delivery_mode" form="reportOrders" class="form-control" title="Тип доставки">
                            <option value="">Тип доставки</option>
                            @foreach($deliveryModes as $key => $deliveryMode)
                                <option
                                        value="{{ $key }}" @selected($key == Request::get('delivery_mode'))>{{ $deliveryMode }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" form="orderReportExportToExcel" name="delivery_mode"
                               value="{{ Request::get('delivery_mode') }}">
                    </div>
                    <div class="col-md-1">
                        <input type="date" form="reportOrders" placeholder="Дата доставки от" title="Дата доставки от"
                               name="date_from_delivery" autocomplete="off"
                               value="{{ Request::get('date_from_delivery') }}" class="form-control">
                        <input type="hidden" form="orderReportExportToExcel" name="date_from_delivery"
                               value="{{ Request::get('date_from_delivery') }}">
                    </div>
                    <div class="col-md-1">
                        <input type="date" form="reportOrders" placeholder="Дата доставки до" title="Дата доставки до"
                               name="date_to_delivery" autocomplete="off" value="{{ Request::get('date_to_delivery') }}"
                               class="form-control">
                        <input type="hidden" form="orderReportExportToExcel" name="date_to_delivery"
                               value="{{ Request::get('date_to_delivery') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="source" form="reportOrders" class="form-control" title="Источник">
                            <option value="">Источник</option>
                            @foreach($sources as $key => $source)
                                <option
                                        value="{{ $key }}" @selected($key == Request::get('source'))>{{ $source }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" form="orderReportExportToExcel" name="source"
                               value="{{ Request::get('source') }}">
                    </div>
                    <div class="col-md-1">
                        <select name="country_id" form="reportOrders" class="form-control" title="Страна">
                            <option value="-1">Страна</option>
                            @foreach($countries as $id => $country)
                                <option
                                        value="{{ $id }}" @selected($id == Request::get('country_id'))>{{ $country }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" form="orderReportExportToExcel" name="country_id"
                               value="{{ Request::get('country_id') }}">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <input type="text" form="reportOrders" placeholder="Клиент" title="Клиент" name="client"
                               autocomplete="off"
                               value="{{ Request::get('client') }}" class="form-control">
                        <input type="hidden" form="orderReportExportToExcel" name="client"
                               value="{{ Request::get('client') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="text" form="reportOrders" placeholder="Адрес" title="Адрес" name="address"
                               autocomplete="off"
                               value="{{ Request::get('address') }}" class="form-control">
                        <input type="hidden" form="orderReportExportToExcel" name="address"
                               value="{{ Request::get('address') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="payment_status" form="reportOrders" class="form-control" title="Статус оплаты">
                            <option value="">Статус оплаты</option>
                            @foreach($paymentStatuses as $key => $paymentStatus)
                                <option
                                        value="{{ $key }}" @selected($key == Request::get('payment_status'))>{{ $paymentStatus }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" form="orderReportExportToExcel" name="payment_status"
                               value="{{ Request::get('payment_status') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="user_id_operator" form="reportOrders" class="form-control single-select"
                                title="Оператор">
                            <option value="">Оператор</option>
                            @foreach($operators as $id => $operator)
                                <option
                                        value="{{ $id }}" @selected($id == Request::get('user_id_operator'))>{{ $operator }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" form="orderReportExportToExcel" name="user_id_operator"
                               value="{{ Request::get('user_id_operator') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="shop_id" form="reportOrders" class="form-control single-select" title="Магазин">
                            <option value="">Магазин</option>
                            @foreach($shops as $id => $shop)
                                <option value="{{ $id }}" @selected($id == Request::get('shop_id'))>{{ $shop }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" form="orderReportExportToExcel" name="shop_id"
                               value="{{ Request::get('shop_id') }}">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <select name="status[]" form="reportOrders" class="form-control multiple-select" title="Статус"
                                multiple>
                            @foreach($statuses as $key => $status)
                                <option
                                        value="{{ $key }}" @selected(in_array($key, Request::get('status') ?? []))>{{ $status }}</option>
                            @endforeach
                        </select>
                        <select name="status[]" form="orderReportExportToExcel" class="form-control d-none"
                                title="Статус"
                                multiple>
                            @foreach($statuses as $key => $status)
                                <option
                                        value="{{ $key }}" @selected(in_array($key, Request::get('status') ?? []))>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <button type="submit" form="reportOrders" class="btn btn-success">
                            <i class="bi bi-search"></i> Вывести
                        </button>

                        <button type="submit" form="orderReportExportToExcel" class="btn btn-primary">
                            <i class="bi bi-file-excel"></i> Выгрузить
                        </button>
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-hover mt-3">
                <thead>
                <tr>
                    <th>Заказ №</th>
                    <th>Дата</th>
                    <th>Доставка</th>
                    <th>Источник</th>
                    <th>Страна</th>
                    <th>Магазин</th>
                    <th>Оператор</th>
                    <th>Клиент</th>
                    <th>Номер</th>
                    <th>Адрес</th>
                    <th>Детали</th>
                    <th>Сумма</th>
                    <th>Оплачено деньгами</th>
                    <th>Оплачено бонусами</th>
                    <th>Стоимость доставки</th>
                    <th>Потрачено в Яндекс</th>
                    <th>Валюта</th>
                    <th>Статус оплаты</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order['order_number'] }}</td>
                        <td>{{ date('d.m.Y H:i', strtotime($order['created_at'])) }}</td>
                        <td>{{ $order['delivery_mode'] }}</td>
                        <td>{{ $order['source'] }}</td>
                        <td>{{ $order['country_name'] }}</td>
                        <td>{{ $order['shop_name'] }}</td>
                        <td>{{ $order['operator_name'] }}</td>
                        <td>{{ $order['client_name'] }}</td>
                        <td>{{ $order['client_phone'] }}</td>
                        <td>{{ $order['address'] }}</td>
                        <td>{{ $order['items'] }}</td>
                        <td>{{ $order['order_price'] }}</td>
                        <td>{{ $order['payment_cash'] }}</td>
                        <td>{{ $order['payment_bonuses'] }}</td>
                        <td>{{ $order['delivery_price'] }}</td>
                        <td>{{ $order['spent_in_yandex'] ?? 0 }}</td>
                        <td>{{ $order['currency_name'] }}</td>
                        <td>{{ $order['payment_status'] }}</td>
                        <td>{{ $order['status'] }}</td>
                        <td>
                            @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
                                <a href="{{ route('admin.orders.show', ['order' => $order]) }}"
                                   type="button"
                                   class="btn btn-success"><i class="bi bi-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="11">Итого</th>
                    <th>{{ $totalOrderPrice }}</th>
                    <th>{{ $totalPaymentCash }}</th>
                    <th>{{ $totalPaymentBonuses }}</th>
                    <th>{{ $totalDeliveryPrice }}</th>
                    <th>{{ $totalPriceInYandex }}</th>
                    <th colspan="4">
                        @if ($orders instanceof Illuminate\Pagination\LengthAwarePaginator)
                            {{ $orders->links() }}
                        @endif
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
