<div class="input-group">
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        form="{{ $form }}"
        value="{{ $value }}"
        class="form-control"
        autocomplete="off"
    >
    <button data-name="{{ $name }}" class="btn btn-outline-danger clean-input">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
