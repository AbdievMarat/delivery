ymaps.ready(init);

function init() {
    let myMap = new ymaps.Map('map', {
        center: [54.9832693, 82.8963831],
        zoom: 15
    });

    let myPlacemark;

    const shop_latitude = $('#latitude').val();
    const shop_longitude = $('#longitude').val();

    if (shop_latitude && shop_longitude) {
        const shop_name = $('#name').val();
        const shop_contact_phone = $('#contact_phone').val();

        myPlacemark = new ymaps.Placemark([shop_latitude, shop_longitude], {
            balloonContent: shop_contact_phone,
            iconCaption: shop_name,
        }, {
            preset: 'islands#bluePersonIcon',
        });

        myMap.setCenter([shop_latitude, shop_longitude], 15);
        myMap.geoObjects.add(myPlacemark);
    }

    // Слушаем клик на карте.
    myMap.events.add('click', function (e) {
        let coords = e.get('coords');

        // Если метка уже создана – просто передвигаем ее.
        if (myPlacemark) {
            myPlacemark.geometry.setCoordinates(coords);
        }
        // Если нет – создаем.
        else {
            myPlacemark = new ymaps.Placemark(coords, {
                    balloonContent: 'Фактические координаты',
                },
                {
                    preset: 'islands#bluePersonIcon',
                });
            myMap.geoObjects.add(myPlacemark);

            // Слушаем событие окончания перетаскивания на метке.
            myPlacemark.events.add('dragend', function () {
                getAddress(myPlacemark.geometry.getCoordinates());
            });
        }
        getAddress(coords);
    });

    //Определяем адрес по координатам (обратное геокодирование).
    function getAddress(coords) {
        myPlacemark.properties.set('iconCaption', 'поиск...');
        ymaps.geocode(coords).then(function (res) {
            let firstGeoObject = res.geoObjects.get(0);

            myPlacemark.properties
                .set({
                    // Формируем строку с данными об объекте.
                    iconCaption: [
                        // Название населенного пункта или вышестоящее административно-территориальное образование.
                        firstGeoObject.getLocalities().length ? firstGeoObject.getLocalities() : firstGeoObject.getAdministrativeAreas(),
                        // Получаем путь до топонима, если метод вернул null, запрашиваем наименование здания.
                        firstGeoObject.getThoroughfare() || firstGeoObject.getPremise()
                    ].filter(Boolean).join(', '),
                    // В качестве контента балуна задаем строку с адресом объекта.
                    balloonContent: firstGeoObject.getAddressLine()
                });

            $('#address').val(firstGeoObject.getAddressLine());
            $('#latitude').val(coords[0]);
            $('#longitude').val(coords[1]);
        });
    }

    //поисковая строка
    new ymaps.SuggestView('address');

    $('#address').bind('blur', function () {
        geocode();
    });

    function geocode() {
        setTimeout(function () {
            let request = $('#address').val();

            if (request === '') {
                $('#latitude').val('');
                $('#longitude').val('');
            } else {
                ymaps.geocode(request).then(function (res) {
                    let obj = res.geoObjects.get(0),
                        error, hint;
                    if (obj) {
                        switch (obj.properties.get('metaDataProperty.GeocoderMetaData.precision')) {
                            case 'exact':
                                break;
                            case 'number':
                            case 'near':
                            case 'range':
                                error = 'Неточный адрес, требуется уточнение';
                                hint = 'Уточните номер дома';
                                break;
                            case 'street':
                                error = 'Неполный адрес, требуется уточнение';
                                hint = 'Уточните номер дома';
                                break;
                            case 'other':
                            default:
                                error = 'Неточный адрес, требуется уточнение';
                                hint = 'Уточните адрес';
                        }
                    } else {
                        error = 'Адрес не найден';
                        hint = 'Уточните адрес';
                    }

                    // Если геокодер возвращает пустой массив или неточный результат, то показываем ошибку.
                    if (error) {
                        showError(error, hint);

                        $('#latitude').val('');
                        $('#longitude').val('');
                    } else {
                        if (myPlacemark) {
                            myPlacemark.geometry.setCoordinates(obj.geometry.getCoordinates());
                        }
                        // Если нет – создаем.
                        else {
                            myPlacemark = new ymaps.Placemark(obj.geometry.getCoordinates(), {
                                    balloonContent: 'Фактические координаты',
                                },
                                {
                                    preset: 'islands#bluePersonIcon',
                                });
                        }
                        myMap.setCenter(obj.geometry.getCoordinates(), 15);
                        myMap.geoObjects.add(myPlacemark);

                        $('#notice').css('display', 'none');
                        $('#address').removeClass('input_error');

                        $('#latitude').val(obj.geometry.getCoordinates()[0]);
                        $('#longitude').val(obj.geometry.getCoordinates()[1]);
                    }
                }, function (e) {
                    console.log(e);
                });
            }
        }, 200);
    }

    function showError(message, hint) {
        $('#notice').text(message + ', ' + hint).css('display', 'block');
        $('#address').addClass('input_error');
    }

    const order_id = $('#order_id').val();
    // если редактирование заказа
    if (order_id) {
        const country_id = $('#country_id').val();

        //получение по адресу координаты из yandex
        //вывод точки адреса, который отпавил клиент по координатам которые выдал яндекс
        // let address = $('#address').val();
        // if (country_id === '2' && address.indexOf('Алматы') === -1)
        //     address = 'Алматы, ' + address;
        // else if (country_id === '3' && address.indexOf('Новосибирск') === -1)
        //     address = 'Новосибирск, ' + address;
        //
        // ymaps.geocode(address)
        //     .then((res) => {
        //         myMap.geoObjects.add(new ymaps.Placemark(
        //             res.geoObjects.get(0).geometry._coordinates,
        //             { iconContent: 'Yandex' },
        //             { preset: 'islands#darkOrangeStretchyIcon' },
        //         ));
        //     });

        const csrf_token = $('meta[name="csrf-token"]').attr('content');
        const status = $('#status').val();

        $.ajax({
            type: 'GET',
            url: '/admin/get_shops_of_country',
            headers: {'X-CSRF-TOKEN': csrf_token},
            data: {country_id},
        }).done(successResponse => {
            // вывод магазинов на карте по координатам
            let shopGeoObject;

            $.each(successResponse.shops, function (index, shop) {
                let shopBalloonContent = `\
                    <h6 class="small">\
                        ${shop.name} <br>\
                        <abbr class="address-line full-width" title="Телефон">Телефон: </abbr>\
                        ${shop.contact_phone}\
                        <br>Время работы: ${shop.work_time_from} - ${shop.work_time_to}\
                    </h6>\
                `;

                shopGeoObject = new ymaps.Placemark([Number(shop.latitude), Number(shop.longitude)], {
                    iconCaption: shop.name,
                    balloonContent: shopBalloonContent,
                }, {
                    preset: 'islands#violetInfoIcon',
                    shop_id: shop.id,
                    shop_name: shop.name,
                    shop_mobile_backend_id: shop.mobile_backend_id
                });

                if (status === 'Новый' || status === 'В магазине' || status === 'У курьера') {
                    shopGeoObject.events.add('click', function (e) {
                        const thisPlacemark = e.get('target');
                        const shop_id = thisPlacemark.options._options.shop_id;

                        const csrf_token = $('meta[name="csrf-token"]').attr('content');
                        const order_id = $('#order_id').val();

                        $.ajax({
                            type: 'GET',
                            url: '/admin/get_remains_products',
                            headers: {'X-CSRF-TOKEN': csrf_token},
                            data: {order_id, shop_id},
                        }).done(successResponse => {
                            $('#container-remains-products').html(successResponse.table);
                            $('.table-products').addClass('d-none');

                            $('#shop_id').val(shop_id);
                        }).fail(errorResponse => {
                            alert('Сервис по остаткам недоступен!');
                        });
                    });
                }

                myMap.geoObjects.add(shopGeoObject);
            });
        }).fail(errorResponse => {
            alert('Не удалось загрузить список магазинов!');
        });

        if (status === 'В магазине' || status === 'У курьера') {
            let courier = [];

            setInterval(function () {
                $.ajax({
                    type: 'GET',
                    url: '/admin/get_driver_position_yandex',
                    headers: {'X-CSRF-TOKEN': csrf_token},
                    data: {order_id, country_id},
                }).done(successResponse => {
                    if (successResponse.driver_positions && successResponse.driver_positions.latitude && successResponse.driver_positions.longitude) {
                        if (courier) {
                            for (let i in courier) {
                                myMap.geoObjects.remove(courier[i]);
                            }
                            courier.length = 0;
                        }

                        const courierGeoObject = new ymaps.Placemark([Number(successResponse.driver_positions.latitude), Number(successResponse.driver_positions.longitude)], {
                            iconContent: 'Курьер',
                        }, {
                            preset: 'islands#darkOrangeAutoIcon',
                        });

                        myMap.geoObjects.add(courierGeoObject);

                        courier.push(courierGeoObject);
                    }
                });
            }, 10000); // каждые 10 секунд
        }
    }
}
