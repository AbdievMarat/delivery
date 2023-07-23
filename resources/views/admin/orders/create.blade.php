@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            Создание заказа
        </div>
        <div class="card-body">
            <form class="row g-3" method="POST" action="{{ route('admin.orders.store') }}">
                @csrf

                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4>Детали заказа</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2 g-1">
                                <div class="col-md-7">Наименование</div>
                                <div class="col-md-2">Кол-во</div>
                                <div class="col-md-3">Цена</div>
                            </div>
                            <div class="row mb-2 g-1 item delivery-item">
                                <div class="col-md-7">
                                    <input type="hidden" name="product_sku[]">
                                    <input type="text" class="form-control product-search" name="product_name[]"
                                           readonly>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control product-amount" name="quantity[]" readonly>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="product_price[]" readonly>
                                </div>
                            </div>
                            @for ($i = 1; $i <= 7; $i++)
                                <div class="row mb-2 g-1 item">
                                    <div class="col-md-7">
                                        <input type="hidden" name="product_sku[]" value="{{ old('product_sku.'.$i) }}">
                                        <input type="text"
                                               class="form-control product-search @error('product_name.'.$i) is-invalid @enderror"
                                               name="product_name[]" value="{{ old('product_name.'.$i) }}"
                                               autocomplete="off">

                                        @error('product_sku.'.$i)
                                        <span class="invalid-feedback"
                                              role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                        @error('product_name.'.$i)
                                        <span class="invalid-feedback"
                                              role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number"
                                               class="form-control product-amount @error('quantity.'.$i) is-invalid @enderror"
                                               name="quantity[]" value="{{ old('quantity.'.$i) }}" min="0">
                                        @error('quantity.'.$i)
                                        <span class="invalid-feedback"
                                              role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text"
                                               class="form-control @error('product_price.'.$i) is-invalid @enderror"
                                               name="product_price[]" value="{{ old('product_price.'.$i) }}" readonly>
                                        @error('product_price.'.$i)
                                        <span class="invalid-feedback"
                                              role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            @endfor
                            <div class="row mb-2 g-1">
                                <div class="col-md-9">
                                    <label for="order_price" class="form-label">Сумма заказа</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="text"
                                           class="form-control @error('order_price') is-invalid @enderror"
                                           name="order_price" id="order_price" value="{{ old('order_price') }}"
                                           readonly>
                                    @error('order_price')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <x-forms.input type="text" name="client_phone" id="client_phone" label="Телефон клиента"
                                           placeholder="Заполните телефон клиента" value="{{ old('client_phone') }}">
                            </x-forms.input>
                        </div>
                        <div class="col-md-6">
                            <x-forms.input type="text" name="client_name" id="client_name" label="Имя клиента"
                                           placeholder="Заполните имя клиента" value="{{ old('client_name') }}">
                            </x-forms.input>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <x-forms.select name="country_id" id="country_id" label="Страна"
                                            :options="$countries"
                                            placeholder="Выберите страну" value="{{ old('country_id') ?? 2 }}">
                            </x-forms.select>
                        </div>
                        <div class="col-8">
                            <x-forms.input type="text" name="address" id="address" label="Адрес"
                                           placeholder="Заполните адрес" value="{{ old('address') }}">
                            </x-forms.input>

                            <p id="notice"></p>

                            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <x-forms.input type="text" name="entrance" id="entrance" label="Подъезд"
                                           placeholder="Заполните подъезд" value="{{ old('entrance') }}">
                            </x-forms.input>
                        </div>
                        <div class="col-md-4">
                            <x-forms.input type="text" name="floor" id="floor" label="Этаж"
                                           placeholder="Заполните номер этажа" value="{{ old('floor') }}">
                            </x-forms.input>
                        </div>
                        <div class="col-md-4">
                            <x-forms.input type="text" name="flat" id="flat" label="Квартира"
                                           placeholder="Заполните номер квартиры" value="{{ old('flat') }}">
                            </x-forms.input>
                        </div>
                    </div>
                    <div class="col-md-12 mt-3" style="height: 250px" id="map"></div>
                </div>

                <div class="col-md-6">
                    <x-forms.select name="delivery_mode" id="delivery_mode" label="Режим доставки"
                                    :options="$deliveryModes"
                                    placeholder="Выберите режим доставки" value="{{ old('delivery_mode') }}">
                    </x-forms.select>
                </div>
                <div class="col-md-4">
                    <x-forms.input type="date" name="delivery_date" id="delivery_date" label="Дата доставки"
                                   placeholder="Заполните дату доставки" value="{{ old('delivery_date') }}">
                    </x-forms.input>
                </div>
                <div class="col-2">
                    <x-forms.input type="time" name="delivery_time" id="delivery_time" label="Время доставки"
                                   placeholder="Заполните время доставки" value="{{ old('delivery_time') }}">
                    </x-forms.input>
                </div>

                <div class="col-md-6">
                    <x-forms.select name="source" id="source" label="Источник"
                                    :options="$sources"
                                    placeholder="Выберите источник" value="{{ old('source') }}">
                    </x-forms.select>
                </div>
                <div class="col-md-6">
                </div>

                <div class="col-md-4">
                    <div class="form-floating">
                            <textarea class="form-control @error('comment_for_operator') is-invalid @enderror"
                                      name="comment_for_operator" placeholder="Заполните комментарий для оператора"
                                      id="comment_for_operator"
                                      style="height: 100px">{{ old('comment_for_operator') }}</textarea>
                        <label for="comment_for_operator">Комментарий для оператора</label>
                        @error('comment_for_operator')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                            <textarea class="form-control @error('comment_for_manager') is-invalid @enderror"
                                      name="comment_for_manager" placeholder="Заполните комментарий для курьера"
                                      id="comment_for_manager"
                                      style="height: 100px">{{ old('comment_for_manager') }}</textarea>
                        <label for="comment_for_manager">Комментарий для магазина</label>
                        @error('comment_for_manager')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                            <textarea class="form-control @error('comment_for_driver') is-invalid @enderror"
                                      name="comment_for_driver" placeholder="Заполните комментарий для курьера"
                                      id="comment_for_driver"
                                      style="height: 100px">{{ old('comment_for_driver') }}</textarea>
                        <label for="comment_for_driver">Комментарий для курьера</label>
                        @error('comment_for_driver')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-success">Добавить</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/css/yandex_map.css', 'resources/js/admin/orders/yandexMap.js', 'resources/js/admin/orders/create.js'])
    @endpush
@endsection
