@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            Создание
        </div>
        <div class="card-body">
            <form class="row g-3" method="POST" action="{{ route('admin.shops.store') }}">
                @csrf

                <div class="col-6">
                    <div class="col-12">
                        <x-forms.input type="text" name="name" id="name" label="Название"
                                       placeholder="Заполните название" value="{{ old('name') }}">
                        </x-forms.input>
                    </div>
                    <div class="col-12">
                        <x-forms.select name="country_id" id="country_id" label="Страна"
                                        :options="$countries"
                                        placeholder="Выберите страну" value="{{ old('country_id') }}">
                        </x-forms.select>
                    </div>
                    <div class="col-12">
                        <x-forms.input type="text" name="mobile_backend_id" id="mobile_backend_id" label="Пин код"
                                       placeholder="Заполните пин код" value="{{ old('mobile_backend_id') }}">
                        </x-forms.input>
                    </div>
                    <div class="col-12">
                        <x-forms.input type="text" name="contact_phone" id="contact_phone" label="Контакты"
                                       placeholder="Заполните контакты" value="{{ old('contact_phone') }}">
                        </x-forms.input>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <x-forms.input type="time" name="work_time_from" id="work_time_from" label="Время работы с"
                                           placeholder="Заполните время работы с" value="{{ old('work_time_from') }}">
                            </x-forms.input>
                        </div>
                        <div class="col-6">
                            <x-forms.input type="time" name="work_time_to" id="work_time_to" label="Время работы до"
                                           placeholder="Заполните время работы до" value="{{ old('work_time_to') }}">
                            </x-forms.input>
                        </div>
                    </div>
                    <div class="col-12">
                        <x-forms.select name="status" id="status" label="Статус"
                                        :options="$statuses"
                                        placeholder="Выберите статус" value="{{ old('status') }}">
                        </x-forms.select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="col-12">
                        <x-forms.input type="text" name="address" id="address" label="Адрес"
                                       placeholder="Заполните адрес" value="{{ old('address') }}">
                        </x-forms.input>

                        <p id="notice"></p>

                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                    </div>
                    <div class="col-12 mt-3" style="height:300px" id="map"></div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success">Добавить</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/css/yandex_map.css', 'resources/js/admin/shops/yandexMap.js'])
    @endpush
@endsection
