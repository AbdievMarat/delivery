@extends('layouts.admin')

@section('content')
    <ul class="nav nav-tabs" role="tablist" xmlns="http://www.w3.org/1999/html">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" data-bs-toggle="tab" href="#edit" aria-selected="true" role="tab">
                Данные заказа
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-bs-toggle="tab" href="#logs" aria-selected="false" role="tab" tabindex="-1">
                Журнал изменений
            </a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="edit" role="tabpanel" aria-labelledby="edit-tab">
            <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-dark mt-2" role="alert">
                                    Заказ № {{ $order->order_number }}
                                </div>
                                <table class="table table-products">
                                    <thead>
                                    <tr>
                                        <th scope="col">Наименование</th>
                                        <th scope="col" style="min-width: 50px;">К-во</th>
                                        <th scope="col">Цена</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->product_price }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                <table class="table table-group-divider">
                                    <tr>
                                        <th scope="col">Сумма заказа</th>
                                        <td class="text-end">{{ $order->order_price }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="col">Оплачено бонусами</th>
                                        <td class="text-end">{{ $order->payment_bonuses }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="col">К оплате</th>
                                        <td class="text-end">{{ $order->payment_cash }}</td>
                                    </tr>
                                </table>

                                @can('copyPaymentUrl', $order)
                                <div class="alert alert-success" role="alert">
                                    <h4 class="alert-heading">Ссылка на оплату сгенерирована!</h4>
                                    <p>Отправьте её клиенту, после успешной оплаты, заказ будет доступен для дальнейшей обработки.<br>Ссылка доступна в течение 1 часа после последнего изменения.</p>
                                    <hr>
                                    <button type="button" class="btn btn-primary" id="copy-button" data-payment_url="{{ $order->payment_url }}">Скопировать ссылку</button>
                                </div>
                                @endcan

                                @can('cancelUnpaid', $order)
                                    @if($order->payment_url)
                                        <div class="alert alert-info mt-2" role="alert">
                                            <h4 class="alert-heading">С момента формирования ссылки прошёл 1 час!</h4>
                                            <p>Вы можете отменить заказ.</p>
                                            <hr>
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
                                        </div>
                                    @endif
                                @endcan

                                @if($order->delivery_mode === App\Enums\DeliveryMode::OnSpecifiedDate->value)
                                    <table class="table table-group-divider">
                                        <tr>
                                            <th scope="col">Режим доставки</th>
                                            <td class="text-end">{{ $order->delivery_mode }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="col">Дата и время доставки</th>
                                            <td class="text-end">{{ date('d.m.Y H:i', strtotime($order->delivery_date)) }}</td>
                                        </tr>
                                    </table>
                                @endif

                                <div class="row">
                                    <label for="status" class="col-sm-4 col-form-label">Статус</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" value="{{ $order->status }}"
                                               disabled="disabled">
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <label for="shop_id" class="col-sm-4 col-form-label">Магазин</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" value="{{ $order->shop->name ?? 'Не выбран' }}"
                                               disabled="disabled">
                                    </div>
                                </div>

                                @can('restorePaid', $order)
                                    <div class="alert alert-info mt-2" role="alert">
                                        <h4 class="alert-heading">Заказ закрыт!</h4>
                                        <p>Для повторной отправки курьера, Вы можете возобновить заказ.<br>Возобновление возможно в течение 24 часов после последнего изменения.</p>
                                        <hr>
                                        <form action="{{ route('admin.restore_paid_order', ['order' => $order]) }}" method="post">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-danger restore-paid-order" title="Возобновить закрытый заказ">Возобновить</button>
                                        </form>
                                    </div>
                                @endcan
                            </div>
                            <div class="col-md-6">
                                <div class="row py-2">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" value="{{ $order->client_phone }}"
                                               disabled="disabled">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" value="{{ $order->client_name }}"
                                               disabled="disabled">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" value="{{ $order->country->name }}"
                                               disabled="disabled">
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" value="{{ $order->address }}"
                                               disabled="disabled">

                                        <input type="hidden" name="latitude" id="latitude"
                                               value="{{ $order->latitude }}">
                                        <input type="hidden" name="longitude" id="longitude"
                                               value="{{ $order->longitude }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Подъезд</label>
                                        <input type="text" class="form-control" value="{{ $order->entrance }}" disabled="disabled">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label">Этаж</label>
                                        <input type="text" class="form-control" value="{{ $order->floor }}" disabled="disabled">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label">Квартира</label>
                                        <input type="text" class="form-control" value="{{ $order->flat }}" disabled="disabled">
                                    </div>
                                </div>
                                <div class="col-12 mt-3" style="height: 300px" id="map"></div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <textarea class="form-control" disabled="disabled" style="height: 150px">{{ $order->comment_for_operator }}</textarea>
                                    <label>Комментарий для оператора</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <textarea class="form-control" disabled="disabled" style="height: 150px">{{ $order->comment_for_manager }}</textarea>
                                    <label>Комментарий для магазина</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <textarea class="form-control" disabled="disabled" style="height: 150px">{{ $order->comment_for_driver }}</textarea>
                                    <label>Комментарий для курьера</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        @include('admin.orders.orders-in-yandex', ['ordersInYandex' => $order->deliveryInYandex, 'deliveryPrice' => $deliveryPrice])
                    </div>
                </div>
        </div>
        <div class="tab-pane fade" id="logs" role="tabpanel" aria-labelledby="logs-tab">
            @include('admin.orders.logs', ['logs' => $order->logs])
        </div>
    </div>

    @push('scripts')
        @vite(['resources/css/yandex_map.css', 'resources/js/admin/orders/yandexMap.js', 'resources/js/admin/orders/show.js'])
    @endpush
@endsection
