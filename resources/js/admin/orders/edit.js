$(() => {
    $(document).on('click', '.cancel-mobile-application-paid-order', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Отменить оплаченный заказ из мобильного приложения?',
            text: 'Введите причину отмены',
            input: 'text',
            inputAttributes: {
                autocapitalize: 'off',
                required: 'true',
                title: 'Заполните причину отмены'
            },
            showCancelButton: true,
            confirmButtonText: 'Отменить',
            showLoaderOnConfirm: true,
            preConfirm: (reason_cancel) => {
                const csrf_token = $('meta[name="csrf-token"]').attr('content');
                const order_id = $('#order_id').val();

                $.ajax({
                    type: "PUT",
                    url: `/admin/cancel_mobile_application_paid_order/${order_id}`,
                    headers: {'X-CSRF-TOKEN': csrf_token},
                    data: {reason_cancel},
                    dataType: "json",
                }).done((successResponse, textStatus) => {
                    window.location.href = "/admin/orders";
                }).fail(errorResponse => {
                    alert('Не удалось отменить заказ!');
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    });

    $(document).on('click', '.cancel-other-paid-order', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Отменить оплаченный прочий заказ?',
            text: 'Введите причину отмены',
            input: 'text',
            inputAttributes: {
                autocapitalize: 'off',
                required: 'true',
                title: 'Заполните причину отмены'
            },
            showCancelButton: true,
            confirmButtonText: 'Отменить',
            showLoaderOnConfirm: true,
            preConfirm: (reason_cancel) => {
                const csrf_token = $('meta[name="csrf-token"]').attr('content');
                const order_id = $('#order_id').val();

                $.ajax({
                    type: "PUT",
                    url: `/admin/cancel_other_paid_order/${order_id}`,
                    headers: {'X-CSRF-TOKEN': csrf_token},
                    data: {reason_cancel},
                    dataType: "json",
                }).done((successResponse, textStatus) => {
                    window.location.href = "/admin/orders";
                }).fail(errorResponse => {
                    alert('Не удалось отменить заказ!');
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    });

    // получение остатков в магазине
    $(document).on('change', '#shop_id', function () {
        const csrf_token = $('meta[name="csrf-token"]').attr('content');
        const order_id = $('#order_id').val();
        const shop_id = $(this).val();

        if (shop_id) {
            $.ajax({
                type: 'GET',
                url: '/admin/get_remains_products',
                headers: {'X-CSRF-TOKEN': csrf_token},
                data: {order_id, shop_id},
            }).done(successResponse => {
                $('#container-remains-products').html(successResponse.table);
                $('.table-products').addClass('d-none');
            }).fail(errorResponse => {
                alert('Сервис по остаткам недоступен!');
            });
        }
    });

    $('#container-remains-products').on('click', '.btn-close', function () {
        $('.table-products').removeClass('d-none');
    });

    // создание заказа в яндекс доставке
    $(document).on('click', '.create-order-yandex', function () {
        const csrf_token = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            type: "POST",
            url: "/admin/store_order_yandex",
            headers: {'X-CSRF-TOKEN': csrf_token},
            dataType: "json",
            data: {
                order_id: $('#order_id').val(),
                shop_id: $('#shop_id').val(),
                address: $('#address').val(),
                latitude: $('#latitude').val(),
                longitude: $('#longitude').val(),
                entrance: $('#entrance').val(),
                floor: $('#floor').val(),
                flat: $('#flat').val(),
                comment_for_operator: $('#comment_for_operator').val(),
                comment_for_manager: $('#comment_for_manager').val(),
                comment_for_driver: $('#comment_for_driver').val()
            },
        }).done((successResponse) => {
            $('#count_of_orders_to_yandex_awaiting_estimate').val(successResponse.count_of_orders_to_yandex_awaiting_estimate);

            $('.span_spinner').addClass('spinner-border').addClass('spinner-border-sm');
            $('.create-order-yandex').addClass('disabled');
            //$('.create-order-yandex').attr('style', 'pointer-events: none; color: #212529; background-color: #eee; border-color: #bdbdbd; opacity: .65;');
        }).fail(errorResponse => {
            $('.is-invalid').each(function () {
                $(this).removeClass('is-invalid');
            });
            $('.invalid-feedback').html('');

            $.each(errorResponse.responseJSON.errors, function (error, error_description) {
                $('#' + error).addClass('is-invalid').after('<div class="invalid-feedback">' + error_description[0] + '</div>');
            });
        });
    });

    // отмена заказа в яндекс доставке
    $(document).on('click', '.cancel-order-yandex', function () {
        const csrf_token = $('meta[name="csrf-token"]').attr('content');
        const order_delivery_in_yandex_id = $(this).data('order_delivery_in_yandex_id');
        const country_id = $('#country_id').val();
        let text_state = 'Бесплатно';
        let cancel_state = 'free';

        $.ajax({
            type: "GET",
            url: "/admin/cancel_info_order_yandex",
            headers: {'X-CSRF-TOKEN': csrf_token},
            dataType: "json",
            data: {
                order_delivery_in_yandex_id,
                country_id
            },
        }).done((successResponse) => {
            cancel_state = successResponse.cancel_state;
            if (cancel_state === 'paid')
                text_state = 'Отмена в Яндекс будет платной!';
            else if (cancel_state === 'unavailable')
                text_state = 'Отмена в Яндекс уже недоступна!';

            Swal.fire({
                title: 'Отменить заказ в Яндекс?',
                text: text_state,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#107ee1',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Да!',
                cancelButtonText: 'Отмена!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "PUT",
                        url: "/admin/cancel_order_yandex",
                        headers: {'X-CSRF-TOKEN': csrf_token},
                        dataType: "json",
                        data: {
                            order_delivery_in_yandex_id,
                            country_id,
                            cancel_state
                        },
                    }).done((successResponse, textStatus) => {
                        if (textStatus === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Заказ в Yandex был отменен!',
                                showConfirmButton: false,
                                timer: 1000
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Заказ в Yandex не был отменен!',
                                showConfirmButton: false,
                                timer: 1000
                            });
                        }
                    }).fail(errorResponse => {
                        alert('Сервис по отмене заказа в Yandex недоступен!');
                    });
                }
            });
        }).fail(errorResponse => {
            alert('Сервис по информации об отмене Yandex недоступен!');
        });
    });

    // отмена заказа в яндекс доставке
    $(document).on('click', '.accept-order-yandex', function () {
        const csrf_token = $('meta[name="csrf-token"]').attr('content');
        const order_delivery_in_yandex_id = $(this).data('order_delivery_in_yandex_id');
        const country_id = $('#country_id').val();

        Swal.fire({
            title: 'Подтвердить заказ в Яндекс?',
            text: 'после подтверждения начнётся поиск машины',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#107ee1',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Да!',
            cancelButtonText: 'Отмена!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "PUT",
                    url: "/admin/accept_order_yandex",
                    headers: {'X-CSRF-TOKEN': csrf_token},
                    dataType: "json",
                    data: {
                        order_delivery_in_yandex_id,
                        country_id
                    },
                }).done((successResponse) => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Заказ в Yandex был подтвержден!',
                        showConfirmButton: false,
                        timer: 1000
                    });
                }).fail(errorResponse => {
                    alert('Сервис по подтверждению заказа Yandex недоступен!');
                });
            }
        });
    });

    setInterval(function () {
        if ($('#count_of_orders_to_yandex_awaiting_estimate').val() > 0 && ($('#status').val() === 'Новый' || $('#status').val() === 'В магазине')) {
            const csrf_token = $('meta[name="csrf-token"]').attr('content');
            const order_id = $('#order_id').val();

            $.ajax({
                type: "GET",
                url: `/admin/get_optimal_order_in_yandex`,
                headers: {'X-CSRF-TOKEN': csrf_token},
                dataType: "json",
                data: {
                    order_id
                },
            }).done((successResponse) => {
                $('#count_of_orders_to_yandex_awaiting_estimate').val(successResponse.count_of_orders_to_yandex_awaiting_estimate);

                if(successResponse.count_of_orders_to_yandex_awaiting_estimate === 0){
                    $('.span_spinner').removeClass('spinner-border').removeClass('spinner-border-sm');
                    $('.create-order-yandex').removeClass('disabled');
                }
            });
        }
    }, 3000); // 3 секунды

    //         // else if($('#order-status').val() === '2' || $('#order-status').val() === '3'){
    //         //     idIntervals2 = setInterval(function() {
    //         //         get_driver_position();
    //         //     }, 10000);//каждые 10 секунд запрашивает местоположение курьера
    //         //     clearInterval(order_delivery_interval);
    //         // }
    //         // else
    //         //     clearInterval(order_delivery_interval);



    const channelOrdersInYandex = pusher.subscribe('ordersInYandex.order.' + $('#order_id').val());
    channelOrdersInYandex.bind('order-list-update-from-yandex', function() {
        get_orders_in_yandex();
    });

    function get_orders_in_yandex() {
        const csrf_token = $('meta[name="csrf-token"]').attr('content');
        const order_id = $('#order_id').val();

        $.ajax({
            type: "GET",
            url: `/admin/get_orders_in_yandex/${order_id}`,
            headers: {'X-CSRF-TOKEN': csrf_token},
            dataType: "json",
        }).done((successResponse) => {
            $('#orders-delivery-in-yandex').replaceWith(successResponse.content);
        }).fail(errorResponse => {
            alert('Не удалось получить заказы Яндекс Доставки!');
        });
    }
});
