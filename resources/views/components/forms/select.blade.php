<label for="{{ $id }}" class="form-label">{{ $label }}</label>
<select
    {!! $attributes->merge(['class' => 'form-select' .($errors->has($name) ? ' is-invalid' : '' ) . ($multiple ? ' multiple-select' : ' single-select')]) !!}
    name="{{ $name . ($multiple ? '[]' : '' )}}"
    id="{{ $id }}"
    @if($multiple)
        multiple
    @endif
>
    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @forelse($options as $key => $option)
        <option value="{{ $key }}" @selected($key == $value)>{{ $option }}</option>
    @empty
        {{ $slot }}
    @endforelse
</select>

@error($name)
<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
@enderror
