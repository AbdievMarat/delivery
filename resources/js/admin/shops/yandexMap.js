ymaps.ready(init);

function init() {
    let myMap = new ymaps.Map('map', {
        center: [54.9832693, 82.8963831],
        zoom: 15
    });

    let myPlacemark;

    const shop_latitude = $('#latitude').val();
    const shop_longitude = $('#longitude').val();

    if(shop_latitude && shop_longitude) {
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

            if(request === ''){
                $('#latitude').val('');
                $('#longitude').val('');
            }
            else{
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
}
