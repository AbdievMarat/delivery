<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PayBoxController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('orders',OrderController::class)->only(['store']);
    Route::get('orders/{id}', [OrderController::class, 'show'])->name('getOrder');
});

Route::post('pay-box-result', [PayBoxController::class, 'result'])->name('payBoxResult');
