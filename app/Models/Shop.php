<?php

namespace App\Models;

use App\Enums\ShopStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string|null $contact_phone
 * @property string|null $work_time_from
 * @property string|null $work_time_to
 * @property string $address
 * @property string $latitude
 * @property string $longitude
 * @property string $mobile_backend_id
 * @property ShopStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Country $country
 * @property-read Order|null $orders
 * @property-read OrderDeliveryInYandex|null $ordersDeliveryInYandex
 *
 * @mixin Builder
 */
class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'name',
        'contact_phone',
        'work_time_from',
        'work_time_to',
        'address',
        'latitude',
        'longitude',
        'mobile_backend_id',
        'status'
    ];

    protected $casts = [
        'status' => ShopStatus::class
    ];

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany
     */
    public function ordersDeliveryInYandex(): HasMany
    {
        return $this->hasMany(OrderDeliveryInYandex::class);
    }

    /**
     * @param Builder $query
     * @param array $data
     * @return void
     */
    public function scopeFilter(Builder $query, array $data): void
    {
        if (isset($data['id'])) {
            $query->where("{$this->getTable()}.id", '=', $data['id']);
        }

        if (isset($data['name'])) {
            $query->where("{$this->getTable()}.name", 'LIKE', '%' . $data['name'] . '%');
        }

        if (isset($data['country_id'])) {
            $query->where("{$this->getTable()}.country_id", '=', $data['country_id']);
        }

        if (isset($data['mobile_backend_id'])) {
            $query->where("{$this->getTable()}.mobile_backend_id", 'LIKE', '%' . $data['mobile_backend_id'] . '%');
        }

        if (isset($data['address'])) {
            $query->where("{$this->getTable()}.address", 'LIKE', '%' . $data['address'] . '%');
        }

        if (isset($data['contact_phone'])) {
            $query->where("{$this->getTable()}.contact_phone", 'LIKE', '%' . $data['contact_phone'] . '%');
        }

        if (isset($data['status'])) {
            $query->where("{$this->getTable()}.status", '=', $data['status']);
        }
    }
}
