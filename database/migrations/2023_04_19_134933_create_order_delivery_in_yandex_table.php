<?php

use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_delivery_in_yandex', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('yandex_id');
            $table->foreignIdFor(Shop::class)->constrained()->noActionOnDelete();
            $table->string('shop_address');
            $table->string('shop_latitude');
            $table->string('shop_longitude');
            $table->string('client_address');
            $table->string('client_latitude');
            $table->string('client_longitude');
            $table->string('tariff')->nullable();
            $table->decimal('offer_price', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2)->default(0);
            $table->string('driver_phone')->nullable();
            $table->string('driver_phone_ext')->nullable();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_delivery_in_yandex');
    }
};
