$(() => {
    $("#copy-button").click(function() {
        const paymentUrl = $(this).data('payment_url');

        copyToClipboard(paymentUrl);

        $(this).attr('title', 'Ссылка скопирована!');
    });

    $(document).on('click', '.restore-paid-order', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Возобновить закрытый заказ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#107ee1',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Да, возобновить!',
            cancelButtonText: 'Отмена!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $(document).on('click', '.cancel-unpaid-order', function (e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Отменить неоплаченный заказ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#107ee1',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Да, отменить!',
            cancelButtonText: 'Отмена!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    function copyToClipboard(text) {
        const dummyElement = $("<textarea>").val(text).css("opacity", "0").appendTo("body").select();

        document.execCommand("copy");

        dummyElement.remove();
    }
});
