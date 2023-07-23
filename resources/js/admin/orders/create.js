$(() => {
    toggleCountry();

    $(document).on('change', '#country_id', function () {
        toggleCountry();
    });
    // получение списка товаров
    $(document).on('keyup', '.product-search', function () {
        const csrf_token = $('meta[name="csrf-token"]').attr('content');
        const country_id = $('#country_id').val();

        $(this).autocomplete({
            source: function (search_product, o) {
                let result = [];

                $.ajax({
                    type: 'GET',
                    url: '/admin/product_search',
                    headers: {'X-CSRF-TOKEN': csrf_token},
                    data: {
                        country_id: country_id,
                        desired_product: search_product.term
                    },
                }).done(successResponse => {
                    $.each(successResponse.data.products, function (index, element) {
                        if (element.art_id.replace(/\s/g, '  ').split("  ").length === 1 && element.price != null)//если в коде товара встречается пробел, не пропускать его и если цена пришла null
                            result.push(element.art_id + '   ' + element.name + '   ' + element.price);
                    });
                    o(result);
                }).fail(errorResponse => {
                    alert('Ошибка при выполнении запроса: ' + errorResponse.status + ' ' + errorResponse.statusText + ' ' + JSON.stringify(JSON.parse(errorResponse.responseText), null, 4));
                });
            },
            minLength: 2,
        });
    });

    // поиск товара, смена количества товара
    $(document).on('blur', '.product-search, .product-amount', function () {
        calculationOrderPrice();
    });

    // расчёт итоговой суммы
    function calculationOrderPrice() {
        let final_price = 0;
        $('.product-search').each(function () {
            let main_div = $(this).closest('.item');

            let product_data = $(this).val().split('   ');
            let product_name = product_data[1];

            if (product_name) {
                let product_code = product_data[0];
                let product_price = product_data[2];
                $(main_div).find('[name="product_name[]"]').val(product_name);
                $(main_div).find('[name="quantity[]"]').val(1);
                $(main_div).find('[name="product_sku[]"]').val(product_code);
                $(main_div).find('[name="product_price[]"]').val(product_price);
            } else if ($(main_div).find('[name="product_price[]"]') === '') {
                $(main_div).find('[name="product_name[]"]').val('');
                $(main_div).find('[name="quantity[]"]').val('');
                $(main_div).find('[name="product_sku[]"]').val('');
                $(main_div).find('[name="product_price[]"]').val('');
            }

            let product_amount = $(main_div).find('[name="quantity[]"]').val();
            let price;

            if (parseInt(product_amount) > 0) {
                price = parseInt($(main_div).find('[name="product_price[]"]').val()) * parseInt(product_amount);
                final_price += price;
            } else if (parseInt(product_amount) <= 0 || product_amount === '') {
                $(main_div).find('[name="product_name[]"]').val('');
                $(main_div).find('[name="quantity[]"]').val('');
                $(main_div).find('[name="product_sku[]"]').val('');
                $(main_div).find('[name="product_price[]"]').val('');
            }
        });

        $('#order_price').val(final_price);
    }

    toggleDeliveryDate();

    $(document).on('change', '#delivery_mode', toggleDeliveryDate);

    function toggleDeliveryDate() {
        if ($('#delivery_mode').val() === 'В указанную дату') {
            $('#delivery_date, label[for="delivery_date"]').removeClass('d-none');
            $('#delivery_time, label[for="delivery_time"]').removeClass('d-none');
        }
        else {
            $('#delivery_date, label[for="delivery_date"]').addClass('d-none');
            $('#delivery_time, label[for="delivery_time"]').addClass('d-none');
        }
    }

    function toggleCountry() {
        const country_id = $('#country_id').val();
        let product_name, product_sku, product_price;

        if (country_id === '1') {
            product_name = 'Доставка';
            product_sku = 'delivery-kg';
            product_price = '160';
        } else if (country_id === '2') {
            product_name = 'Доставка';
            product_sku = 'delivery-kz';
            product_price = '1500';
        } else if (country_id === '3') {
            product_name = 'Доставка';
            product_sku = 'delivery-ru';
            product_price = '350';
        }

        $('.delivery-item').find('[name="product_name[]"]').val(product_name);
        $('.delivery-item').find('[name="quantity[]"]').val(1);
        $('.delivery-item').find('[name="product_sku[]"]').val(product_sku);
        $('.delivery-item').find('[name="product_price[]"]').val(product_price);
    }
});
