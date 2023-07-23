@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Магазины</div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <div></div>
                <div>
                    <a href="{{ route('admin.shops.create') }}" class="btn btn-success float-end">Создать</a>
                </div>
            </div>

            <form action="{{ route('admin.shops.index') }}" id="search" method="GET"></form>

            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Название</th>
                    <th>Страна</th>
                    <th>Пин код</th>
                    <th>Адрес</th>
                    <th>Контакты</th>
                    <th>Время работы</th>
                    <th>Статус</th>
                    <th style="width: 160px"></th>
                </tr>
                <tr>
                    <th>
                        <x-input-search type="text" name="id" form="search" value="{{ Request::get('id') }}">
                        </x-input-search>
                    </th>
                    <th>
                        <x-input-search type="text" name="name" form="search" value="{{ Request::get('name') }}">
                        </x-input-search>
                    </th>
                    <th>
                        <x-select-search name="country_id" form="search" :options="$countries"
                                         value="{{ Request::get('country_id') }}">
                        </x-select-search>
                    </th>
                    <th>
                        <x-input-search type="text" name="mobile_backend_id" form="search"
                                        value="{{ Request::get('mobile_backend_id') }}">
                        </x-input-search>
                    </th>
                    <th>
                        <x-input-search type="text" name="address" form="search"
                                        value="{{ Request::get('address') }}">
                        </x-input-search>
                    </th>
                    <th>
                        <x-input-search type="text" name="contact_phone" form="search"
                                        value="{{ Request::get('contact_phone') }}">
                        </x-input-search>
                    </th>
                    <th></th>
                    <th>
                        <x-select-search name="status" form="search" :options="$statuses"
                                         value="{{ Request::get('status') }}">
                        </x-select-search>
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($shops as $shop)
                    <tr>
                        <td>{{ $shop->id }}</td>
                        <td>{{ $shop->name }}</td>
                        <td>{{ $shop->country_name }}</td>
                        <td>{{ $shop->mobile_backend_id }}</td>
                        <td>{{ $shop->address }}</td>
                        <td>{{ $shop->contact_phone }}</td>
                        <td>{{ $shop->work_time_from }} - {{ $shop->work_time_to }}</td>
                        <td>{{ $shop->status }}</td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <div>
                                    <a href="{{ route('admin.shops.show', ['shop' => $shop]) }}" type="button"
                                       class="btn btn-success"><i class="bi bi-eye"></i></a>
                                </div>
                                <div class="mx-2">
                                    <a href="{{ route('admin.shops.edit', ['shop' => $shop]) }}" type="button"
                                       class="btn btn-warning"><i class="bi bi-pencil-square"></i></a>
                                </div>
                                @can('delete', $shop)
                                    <form action="{{ route('admin.shops.destroy', ['shop' => $shop]) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger delete-entry"><i
                                                    class="bi bi-trash3"></i></button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="8">
                        {{ $shops->links() }}
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
