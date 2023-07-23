@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            Редактирование
        </div>
        <div class="card-body">
            <form class="row g-3" method="POST" action="{{ route('admin.users.update', ['user' => $user]) }}">
                @method('PUT')
                @csrf

                <div class="col-md-6">
                    <div class="col-md-12">
                        <x-forms.input type="text" name="name" id="name" label="Имя"
                                       placeholder="Заполните имя" value="{!! $user->name !!}">
                        </x-forms.input>
                    </div>
                    <div class="col-md-12">
                        <x-forms.input type="text" name="email" id="email" label="Логин"
                                       placeholder="Заполните логин" value="{{ old('email') ?? $user->email }}">
                        </x-forms.input>
                    </div>
                    <div class="col-md-12">
                        <x-forms.select name="role_id" id="role_id" label="Роль"
                                        :options="$roles"
                                        placeholder="Выберите роль" value="{{ old('role_id') ?? $user->roles[0]->id }}">
                        </x-forms.select>
                    </div>
                    <div class="col-md-12">
                        <x-forms.select name="available_countries" id="available_countries" label="Доступные страны"
                                        placeholder="Выберите страны" multiple>
                            @foreach($countries as $country_id => $country_name)
                                <option
                                    @selected(in_array($country_id, $user->available_countries ?? [])) value="{{ $country_id }}">
                                    {{ $country_name }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="col-md-12">
                        <x-forms.input type="password" name="password" id="password" label="Пароль"
                                       placeholder="Заполните пароль">
                        </x-forms.input>
                    </div>
                    <div class="col-md-12">
                        <x-forms.input type="password" name="password_confirmation" id="password_confirmation"
                                       label="Подтверждение пароля"
                                       placeholder="Подтвердите пароль">
                        </x-forms.input>
                    </div>
                    <div class="col-md-12">
                        <x-forms.select name="attached_shops" id="attached_shops" label="Прикрепленные магазины"
                                        placeholder="Выберите магазин" multiple>
                            @foreach($shops as $shop_id => $shop_name)
                                <option
                                    @selected(in_array($shop_id, $user->attached_shops ?? [])) value="{{ $shop_id }}">
                                    {{ $shop_name }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-check">
                        <input type="checkbox" name="active"
                               class="form-check-input @error('active') is-invalid @enderror"
                               id="active" @checked(old('active') ?? $user->active)>
                        <label class="form-check-label" for="active">Активный</label>

                        @error('active')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-success">Обновить</button>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
        @vite(['resources/js/admin/users/createEdit.js'])
    @endpush
@endsection
