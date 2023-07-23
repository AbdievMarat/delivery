$(() => {
    toggleRole();

    $(document).on('change', '#role_id', toggleRole);

    function toggleRole() {
        if ($('#role_id').val() === '3') {
            $('label[for="attached_shops"]').removeClass('d-none');
            $('#attached_shops').next().removeClass('d-none');
        } else {
            $('label[for="attached_shops"]').addClass('d-none');
            $('#attached_shops').next().addClass('d-none');
        }

        if ($('#role_id').val() === '5') {
            $('label[for="available_countries"]').removeClass('d-none');
            $('#available_countries').next().removeClass('d-none');
        } else {
            $('label[for="available_countries"]').addClass('d-none');
            $('#available_countries').next().addClass('d-none');
        }
    }
});
