@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Страны</div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <div></div>
                <div>
                    <a href="{{ route('admin.countries.create') }}" class="btn btn-success float-end">Создать</a>
                </div>
            </div>

            <table id="countries-table" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Название</th>
                    <th>Валюта</th>
                    <th>Полное название</th>
                    <th>Контакты</th>
                    <th>Тарифы Яндекс</th>
                    <th>Статус</th>
                    <th style="width: 160px"></th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($countries as $country)
                    <tr>
                        <td>{{ $country->id }}</td>
                        <td>{{ $country->name }}</td>
                        <td>{{ $country->currency_name }}</td>
                        <td>{{ $country->organization_name }} </td>
                        <td>{{ $country->contact_phone }}</td>
                        <td>
                            @forelse (!empty($country->yandex_tariffs) ? $country->yandex_tariffs : [] as $tariff)
                                <button class="disabled btn btn-primary">{{ $tariff }}</button>
                            @empty
                                Нет тарифов
                            @endforelse
                        </td>
                        <td>{{ $country->status }}</td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <div>
                                    <a href="{{ route('admin.countries.show', ['country' => $country]) }}" type="button"
                                       class="btn btn-success"><i class="bi bi-eye"></i></a>
                                </div>
                                <div class="mx-2">
                                    <a href="{{ route('admin.countries.edit', ['country' => $country]) }}" type="button"
                                       class="btn btn-warning"><i class="bi bi-pencil-square"></i></a>
                                </div>
                                @can('delete', $country)
                                    <form action="{{ route('admin.countries.destroy', ['country' => $country]) }}"
                                          method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger delete-entry">
                                            <i class="bi bi-trash3"></i></button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
