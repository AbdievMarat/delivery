@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            Создание
        </div>
        <div class="card-body">
            <form class="row g-3" method="POST" action="{{ route('admin.countries.store') }}">
                @csrf

                <div class="col-6">
                    <x-forms.input type="text" name="name" id="name" label="Название"
                                   placeholder="Заполните название" value="{{ old('name') }}">
                    </x-forms.input>
                </div>
                <div class="col-6">
                    <x-forms.input type="text" name="organization_name" id="organization_name" label="Полное название"
                                   placeholder="Заполните полное название" value="{{ old('organization_name') }}">
                    </x-forms.input>
                </div>
                <div class="col-6">
                    <x-forms.input type="text" name="currency_name" id="currency_name" label="Валюта"
                                   placeholder="Заполните название валюты" value="{{ old('currency_name') }}">
                    </x-forms.input>
                </div>
                <div class="col-6">
                    <x-forms.input type="text" name="currency_iso" id="currency_iso" label="Код валюты"
                                   placeholder="Заполните код валюты" value="{{ old('currency_iso') }}">
                    </x-forms.input>
                </div>
                <div class="col-6">
                    <x-forms.input type="text" name="contact_phone" id="contact_phone" label="Контакты"
                                   placeholder="Заполните контакты" value="{{ old('contact_phone') }}">
                    </x-forms.input>
                </div>
                <div class="col-6">
                    <x-forms.select name="yandex_tariffs" id="yandex_tariffs" label="Тарифы Яндекс"
                                    placeholder="Выберите тариф" multiple>
                        @foreach($yandexTariffs as $tariff_key => $tariff)
                            <option
                                @selected(in_array($tariff_key, old('yandex_tariffs') ?? [])) value="{{ $tariff_key }}">
                                {{ $tariff }}
                            </option>
                        @endforeach
                    </x-forms.select>
                </div>
                <div class="col-6">
                    <x-forms.select name="status" id="status" label="Статус"
                                    :options="$statuses"
                                    placeholder="Выберите статус" value="{{ old('status') }}">
                    </x-forms.select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">Добавить</button>
                </div>
            </form>
        </div>
    </div>
@endsection
