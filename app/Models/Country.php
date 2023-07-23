<?php

namespace App\Models;

use App\Enums\CountryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'currency_name',
        'currency_iso',
        'organization_name',
        'contact_phone',
        'yandex_tariffs',
        'status'
    ];

    protected $casts = [
        'status' => CountryStatus::class,
        'yandex_tariffs' => 'json',
    ];

    /**
     * @return HasMany
     */
    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
