@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ url()->previous() }}" class="btn btn-primary btn-sm" title="Назад">
                <i class="bi bi-arrow-left"></i>
            </a>
            Просмотр
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-4">Создано</dt>
                <dd class="col-sm-8">{{ date('d.m.Y H:i', strtotime($shop->created_at)) }}</dd>
                <dt class="col-sm-4">Изменёно</dt>
                <dd class="col-sm-8">{{ date('d.m.Y H:i', strtotime($shop->updated_at)) }}</dd>
                <dt class="col-sm-4">Наименование</dt>
                <dd class="col-sm-8">{{ $shop->name }}</dd>
                <dt class="col-sm-4">Страна</dt>
                <dd class="col-sm-8">{{ $shop->country->name }}</dd>
                <dt class="col-sm-4">Пин код</dt>
                <dd class="col-sm-8">{{ $shop->mobile_backend_id }}</dd>
                <dt class="col-sm-4">Адрес</dt>
                <dd class="col-sm-8">{{ $shop->address }}</dd>
                <dt class="col-sm-4">Контакты</dt>
                <dd class="col-sm-8">{{ $shop->contact_phone }}</dd>
                <dt class="col-sm-4">Время работы</dt>
                <dd class="col-sm-8">{{ $shop->work_time_from }} - {{ $shop->work_time_to }}</dd>
                <dt class="col-sm-4">Статус</dt>
                <dd class="col-sm-8">{{ $shop->status }}</dd>
            </dl>
        </div>
    </div>
@endsection
