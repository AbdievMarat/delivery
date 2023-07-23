<?php

use App\Enums\DeliveryMode;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Country;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_number')->unique();
            $table->string('mobile_backend_callback_url')->nullable();
            $table->string('client_phone');
            $table->string('client_name');
            $table->foreignIdFor(Country::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('address');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('entrance')->nullable();
            $table->string('floor')->nullable();
            $table->string('flat')->nullable();
            $table->decimal('order_price', 10, 2)->default(0);
            $table->decimal('payment_cash', 10, 2)->default(0);
            $table->decimal('payment_bonuses', 10, 2)->default(0);
            $table->enum('payment_status', [array_column(PaymentStatus::cases(), 'value')])->default(PaymentStatus::Unpaid->value);
            $table->string('payment_url')->nullable();
            $table->text('comment_for_operator')->nullable();
            $table->timestamp('operator_deadline_date')->nullable();
            $table->timestamp('operator_real_date')->nullable();
            $table->unsignedBigInteger('user_id_operator')->nullable();
            $table->foreign('user_id_operator')->references('id')->on('users')->noActionOnDelete();
            $table->text('comment_for_manager')->nullable();
            $table->timestamp('manager_deadline_date')->nullable();
            $table->timestamp('manager_real_date')->nullable();
            $table->unsignedBigInteger('user_id_manager')->nullable();
            $table->foreign('user_id_manager')->references('id')->on('users')->noActionOnDelete();
            $table->text('comment_for_driver')->nullable();
            $table->timestamp('driver_deadline_date')->nullable();
            $table->timestamp('driver_real_date')->nullable();
            $table->unsignedBigInteger('user_id_driver')->nullable();
            $table->foreign('user_id_driver')->references('id')->on('users')->noActionOnDelete();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops')->noActionOnDelete();
            $table->enum('source', [array_column(OrderSource::cases(), 'value')])->default(OrderSource::MobileApp->value);
            $table->enum('delivery_mode', [array_column(DeliveryMode::cases(), 'value')])->default(DeliveryMode::SoonAsPossible->value);
            $table->timestamp('delivery_date')->nullable();
            $table->enum('status', [array_column(OrderStatus::cases(), 'value')])->default(OrderStatus::New->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
