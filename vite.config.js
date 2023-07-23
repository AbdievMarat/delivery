import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/css/yandex_map.css',
                'resources/js/admin/shops/yandexMap.js',
                'resources/js/admin/orders/index.js',
                'resources/js/admin/orders/create.js',
                'resources/js/admin/orders/edit.js',
                'resources/js/admin/orders/show.js',
                'resources/js/admin/orders/liveOrders.js',
                'resources/js/admin/orders/yandexMap.js',
                'resources/js/admin/users/createEdit.js',
                'resources/js/shop/orders/index.js'
            ],
            refresh: true,
        }),
    ],
});
