<?php

use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\YandexOrderController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\OrderReportController;
use App\Http\Controllers\Shop\IssuedOrdersController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,operator'])->group(function () {
    Route::resources([
        'users' => UserController::class,
        'countries' => CountryController::class,
        'shops' => ShopController::class,
    ]);
    Route::get('get_shops_of_country/{country}', [CountryController::class, 'getShopsOfCountry'])->name('get_shops_of_country');

    Route::get('product_search', [ProductController::class, 'search'])->name('product_search');
    Route::get('get_remains_products', [ProductController::class, 'getRemainsProducts'])->name('get_remains_products');

    Route::resource('orders',OrderController::class)->except(['destroy']);
    Route::get('live_orders', [OrderController::class, 'liveOrders'])->name('live_orders');
    Route::put('cancel_unpaid_order/{order}', [OrderController::class, 'cancelUnpaidOrder'])->name('cancel_unpaid_order');
    Route::put('restore_paid_order/{order}', [OrderController::class, 'restorePaidOrder'])->name('restore_paid_order');
    Route::put('cancel_mobile_application_paid_order/{order}', [OrderController::class, 'cancelMobileApplicationPaidOrder'])->name('cancel_mobile_application_paid_order');
    Route::put('cancel_other_paid_order/{order}', [OrderController::class, 'cancelOtherPaidOrder'])->name('cancel_other_paid_order');

    Route::post('store_order_yandex/{order}', [YandexOrderController::class, 'storeOrderYandex'])->name('store_order_yandex');
    Route::get('cancel_info_order_yandex', [YandexOrderController::class, 'cancelInfoOrderYandex'])->name('cancel_info_order_yandex');
    Route::put('cancel_order_yandex', [YandexOrderController::class, 'cancelOrderYandex'])->name('cancel_order_yandex');
    Route::put('accept_order_yandex', [YandexOrderController::class, 'acceptOrderYandex'])->name('accept_order_yandex');
    Route::get('get_orders_in_yandex/{order}', [YandexOrderController::class, 'getOrdersInYandex'])->name('get_orders_in_yandex');
    Route::get('get_optimal_order_in_yandex/{order}', [YandexOrderController::class, 'getOptimalOrderInYandex'])->name('get_optimal_order_in_yandex');
    Route::get('get_driver_position_yandex', [YandexOrderController::class, 'getDriverPositionYandex'])->name('get_driver_position_yandex');
});

Route::prefix('shop')->name('shop.')->middleware(['auth', 'role:manager'])->group(function () {
    Route::resource('orders',App\Http\Controllers\Shop\OrderController::class)->only(['index']);
    Route::put('transfer_order_to_driver/{order}', [App\Http\Controllers\Shop\OrderController::class, 'transferOrderToDriver'])->name('transfer_order_to_driver');
    Route::get('issued_orders', [IssuedOrdersController::class, 'index'])->name('issued_orders');
});

Route::middleware(['auth', 'role:admin,operator,accountant'])->group(function () {
    Route::get('order_report', [OrderReportController::class, 'index'])->name('order_report');
    Route::get('order_report_export_to_excel', [OrderReportController::class, 'exportToExcel'])->name('order_report_export_to_excel');
});
