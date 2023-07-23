@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.countries.index') }}" class="btn btn-primary btn-sm" title="Назад">
                <i class="bi bi-arrow-left"></i>
            </a>
            Просмотр
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-4">Создано</dt>
                <dd class="col-sm-8">{{ date('d.m.Y H:i', strtotime($country->created_at)) }}</dd>
                <dt class="col-sm-4">Изменёно</dt>
                <dd class="col-sm-8">{{ date('d.m.Y H:i', strtotime($country->updated_at)) }}</dd>
                <dt class="col-sm-4">Наименование</dt>
                <dd class="col-sm-8">{{ $country->name }}</dd>
                <dt class="col-sm-4">Валюта</dt>
                <dd class="col-sm-8">{{ $country->currency_name }}</dd>
                <dt class="col-sm-4">Полное название</dt>
                <dd class="col-sm-8">{{ $country->organization_name }}</dd>
                <dt class="col-sm-4">Контакты</dt>
                <dd class="col-sm-8">{{ $country->contact_phone }}</dd>
                <dt class="col-sm-4">Широта</dt>
                <dd class="col-sm-8">{{ $country->latitude }}</dd>
                <dt class="col-sm-4">Долгота</dt>
                <dd class="col-sm-8">{{ $country->longitude }}</dd>
                <dt class="col-sm-4">Статус</dt>
                <dd class="col-sm-8">{{ $country->status }}</dd>
            </dl>
        </div>
    </div>
@endsection
