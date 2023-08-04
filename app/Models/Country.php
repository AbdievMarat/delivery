<?php

namespace App\Models;

use App\Enums\CountryStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $currency_name
 * @property string $currency_iso
 * @property string $organization_name
 * @property string $contact_phone
 * @property string|null $yandex_tariffs
 * @property string $longitude
 * @property string $mobile_backend_id
 * @property CountryStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Shop|null $shops
 * @property-read Order|null $orders
 *
 * @mixin Builder
 */
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

    const KYRGYZSTAN_COUNTRY_ID = 1;
    const KAZAKHSTAN_COUNTRY_ID = 2;
    const RUSSIA_COUNTRY_ID = 3;

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
