<?php

use App\Enums\CountryStatus;
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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('currency_name');
            $table->string('currency_iso');
            $table->string('organization_name');
            $table->string('contact_phone');
            $table->string('yandex_tariffs')->nullable();
            $table->enum('status', [array_column(CountryStatus::cases(), 'value')])->default(CountryStatus::Active->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
