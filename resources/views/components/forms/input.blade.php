<label for="{{ $id }}" class="form-label">{{ $label }}</label>

<input
    {{ $attributes->merge(['class' => 'form-control' .($errors->has($name) ? ' is-invalid' : '')]) }}
    type="{{ $type }}"
    name="{{ $name }}"
    id="{{ $id }}"
    placeholder="{{ $placeholder }}"
    value="{{ $value }}"
>

@error($name)
<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
@enderror
