@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Пользователи</div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <div></div>
                <div>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success float-end">Создать</a>
                </div>
            </div>

            <form action="{{ route('admin.users.index') }}" id="search" method="GET"></form>

            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Имя</th>
                    <th>Логин</th>
                    <th>Роль</th>
                    <th>Активный</th>
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
                        <x-input-search type="text" name="email" form="search" value="{{ Request::get('email') }}">
                        </x-input-search>
                    </th>
                    <th></th>
                    <th>
                        <x-select-search name="active" form="search" :options="['1' => 'да', '0' => 'нет']"
                                         value="{{ Request::get('active') }}">
                        </x-select-search>
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @forelse($user->roles as $role)
                                <button class="disabled btn btn-primary">{{ $role->description }}</button>
                            @empty
                                Нет ролей
                            @endforelse
                        </td>
                        <td>{{ $user->active ? 'Да' : 'Нет' }}</td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <div>
                                    <a href="{{ route('admin.users.show', ['user' => $user]) }}" type="button"
                                       class="btn btn-success"><i class="bi bi-eye"></i></a>
                                </div>
                                @can('update', $user)
                                    <div class="mx-2">
                                        <a href="{{ route('admin.users.edit', ['user' => $user]) }}" type="button"
                                           class="btn btn-warning"><i class="bi bi-pencil-square"></i></a>
                                    </div>
                                @endcan
                                @can('delete', $user)
                                    <form action="{{ route('admin.users.destroy', ['user' => $user]) }}" method="post">
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
                        {{ $users->links() }}
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
