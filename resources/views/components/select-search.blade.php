<div class="input-group">
    <select
        form="{{ $form }}"
        name="{{ $name }}"
        class="form-select"
    >
        <option value=""></option>
        @foreach($options as $key => $option)
            <option value="{{ $key }}" @selected($key == $value)>{{ $option }}</option>
        @endforeach
    </select>
    <button data-name="{{ $name }}" class="btn btn-outline-danger clean-input">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
