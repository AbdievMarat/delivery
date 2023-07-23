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
            <form class="g-3" method="POST" action="{{ route('admin.orders.update', ['order' => $order]) }}">
                @method('PUT')
                @csrf

                <input type="hidden" id="order_id" value="{{ $order->id }}">
                <input type="hidden" id="country_id" value="{{ $order->country_id }}">
                <input type="hidden" id="count_of_orders_to_yandex_awaiting_estimate"
                       value="{{ $count_of_orders_to_yandex_awaiting_estimate }}">

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

                                <div id="container-remains-products"></div>

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

                                @if($order->delivery_mode === App\Enums\DeliveryMode::OnSpecifiedDate->value)
                                    <div class="row mb-2">
                                        <div class="col-md-8">
                                            <x-forms.input type="date" name="delivery_date" id="delivery_date"
                                                           label="Дата доставки"
                                                           placeholder="Заполните дату доставки"
                                                           value="{{ old('delivery_date') ?? date('Y-m-d', strtotime($order->delivery_date)) }}">
                                            </x-forms.input>
                                        </div>
                                        <div class="col-4">
                                            <x-forms.input type="time" name="delivery_time" id="delivery_time"
                                                           label="Время доставки"
                                                           placeholder="Заполните время доставки"
                                                           value="{{ old('delivery_time') ?? date('H:i', strtotime($order->delivery_date)) }}">
                                            </x-forms.input>
                                        </div>
                                    </div>
                                @endif

                                <div class="row">
                                    <label for="status" class="col-sm-4 col-form-label">Статус</label>
                                    <div class="col-md-8">
                                        <select name="status" class="form-select @error('status') is-invalid @enderror"
                                                id="status">
                                            @foreach($statuses as $key => $status)
                                                <option
                                                    value="{{ $key }}" @selected($key == (old('status') ?? $order->status ))>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <label for="shop_id" class="col-sm-4 col-form-label">Магазин</label>
                                    <div class="col-md-8">
                                        <select name="shop_id"
                                                class="form-select @error('shop_id') is-invalid @enderror" id="shop_id">
                                            <option value="">Выберите</option>
                                            @foreach($shops as $key => $shop)
                                                <option
                                                    value="{{ $key }}" @selected($key == (old('shop_id') ?? $order->shop_id ))>{{ $shop }}</option>
                                            @endforeach
                                        </select>
                                        @error('shop_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                @can('cancelMobileApplicationPaid', $order)
                                    <div class="alert alert-info mt-2" role="alert">
                                        <h4 class="alert-heading">Отменить заказ № {{ $order->order_number }}</h4>
                                        <p>Клиенту вернуться денежные средства и начисленные баллы будут вычтены в мобильном приложение.</p>
                                        <hr>
                                        <button type="submit" class="btn btn-danger cancel-mobile-application-paid-order" title="Отменить оплаченный заказ из мобильного приложения">Отменить оплаченный заказ из мобильного приложения</button>
                                    </div>
                                @endcan

                                @can('cancelOtherPaid', $order)
                                    <div class="alert alert-info mt-2" role="alert">
                                        <h4 class="alert-heading">Отменить заказ № {{ $order->order_number }}</h4>
                                        <p>Клиенту вернуться денежные средства.</p>
                                        <hr>
                                        <button type="submit" class="btn btn-danger cancel-other-paid-order" title="Отменить оплаченный прочий заказ">Отменить оплаченный прочий заказ</button>
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
                                        <input type="text" name="address"
                                               class="form-control @error('address') is-invalid @enderror" id="address"
                                               value="{{ old('address') ?? $order->address }}">
                                        @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror

                                        <p id="notice"></p>

                                        <input type="hidden" name="latitude" id="latitude"
                                               value="{{ $order->latitude }}">
                                        <input type="hidden" name="longitude" id="longitude"
                                               value="{{ $order->longitude }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-forms.input type="text" name="entrance" id="entrance" label="Подъезд"
                                                       placeholder="Заполните подъезд"
                                                       value="{{ old('entrance') ?? $order->entrance }}">
                                        </x-forms.input>
                                    </div>
                                    <div class="col-4">
                                        <x-forms.input type="text" name="floor" id="floor" label="Этаж"
                                                       placeholder="Заполните номер этажа"
                                                       value="{{ old('floor') ?? $order->floor }}">
                                        </x-forms.input>
                                    </div>
                                    <div class="col-4">
                                        <x-forms.input type="text" name="flat" id="flat" label="Квартира"
                                                       placeholder="Заполните номер квартиры"
                                                       value="{{ old('flat') ?? $order->flat }}">
                                        </x-forms.input>
                                    </div>
                                </div>
                                <div class="col-12 mt-3" style="height: 300px" id="map"></div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <textarea class="form-control @error('comment_for_operator') is-invalid @enderror"
                                              name="comment_for_operator"
                                              placeholder="Заполните комментарий для оператора"
                                              id="comment_for_operator"
                                              style="height: 150px">{{ old('comment_for_operator') ?? $order->comment_for_operator }}</textarea>
                                    <label for="comment_for_operator">Комментарий для оператора</label>
                                    @error('comment_for_operator')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <textarea class="form-control @error('comment_for_manager') is-invalid @enderror"
                                              name="comment_for_manager" placeholder="Заполните комментарий для курьера"
                                              id="comment_for_manager"
                                              style="height: 150px">{{ old('comment_for_manager') ?? $order->comment_for_manager }}</textarea>
                                    <label for="comment_for_manager">Комментарий для магазина</label>
                                    @error('comment_for_manager')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <textarea class="form-control @error('comment_for_driver') is-invalid @enderror"
                                              name="comment_for_driver" placeholder="Заполните комментарий для курьера"
                                              id="comment_for_driver"
                                              style="height: 150px">{{ old('comment_for_driver') ?? $order->comment_for_driver }}</textarea>
                                    <label for="comment_for_driver">Комментарий для курьера</label>
                                    @error('comment_for_driver')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-success w-100">Обновить</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        @include('admin.orders.orders-in-yandex', ['ordersInYandex' => $order->deliveryInYandex, 'deliveryPrice' => $deliveryPrice])

                        <button type="button" class="btn btn-success w-100 create-order-yandex">
                            Отправить заказ в Яндекс доставку
                            <span class="span_spinner"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="tab-pane fade" id="logs" role="tabpanel" aria-labelledby="logs-tab">
            @include('admin.orders.logs', ['logs' => $order->logs])
        </div>
    </div>

    @push('scripts')
        @vite(['resources/css/yandex_map.css', 'resources/js/admin/orders/yandexMap.js', 'resources/js/admin/orders/edit.js'])
    @endpush
@endsection
