<?php

namespace App\Models;

use App\Enums\ShopStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * @param Builder $query
     * @return void
     */
    public function scopeFilter(Builder $query): void
    {
        $query->when(request('id'), function (Builder $q) {
            $q->where("{$this->getTable()}.id", '=', request('id'));
        });

        $query->when(request('name'), function (Builder $q) {
            $q->where("{$this->getTable()}.name", 'LIKE', '%' . request('name') . '%');
        });

        $query->when(request('country_id'), function (Builder $q) {
            $q->where("{$this->getTable()}.country_id", '=', request('country_id'));
        });

        $query->when(request('mobile_backend_id'), function (Builder $q) {
            $q->where("{$this->getTable()}.mobile_backend_id", 'LIKE', '%' . request('mobile_backend_id') . '%');
        });

        $query->when(request('address'), function (Builder $q) {
            $q->where("{$this->getTable()}.address", 'LIKE', '%' . request('address') . '%');
        });

        $query->when(request('contact_phone'), function (Builder $q) {
            $q->where("{$this->getTable()}.contact_phone", 'LIKE', '%' . request('contact_phone') . '%');
        });

        $query->when(request('status'), function (Builder $q) {
            $q->where("{$this->getTable()}.status", '=', request('status'));
        });
    }
}
