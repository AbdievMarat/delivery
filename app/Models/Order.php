<?php

namespace App\Models;

use App\Enums\OrderStatus;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    use HasFactory;

    const OPERATOR_DEADLINE_IN_MINUTES = 16;
    const MANAGER_DEADLINE_IN_MINUTES = 18;
    const DRIVER_DEADLINE_IN_MINUTES = 21;

    protected $fillable = [
        'order_number',
        'client_phone',
        'client_name',
        'country_id',
        'address',
        'latitude',
        'longitude',
        'entrance',
        'floor',
        'flat',
        'delivery_mode',
        'delivery_date',
        'source',
        'comment_for_operator',
        'comment_for_manager',
        'comment_for_driver',
        'order_price',
        'payment_cash',
        'payment_bonuses',
        'payment_status',
        'status',
        'shop_id',
        'driver_real_date',
    ];

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return BelongsTo
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * @return BelongsTo
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id_operator');
    }

    /**
     * @return BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id_manager');
    }

    /**
     * @return BelongsTo
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id_driver');
    }

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany
     */
    public function deliveryInYandex(): HasMany
    {
        return $this->hasMany(OrderDeliveryInYandex::class);
    }

    /**
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(OrderLog::class);
    }

    /**
     * @param Builder $query
     * @param null $managerRealDateFrom
     * @param null $managerRealDateTo
     * @return void
     */
    public function scopeFilter(Builder $query, $managerRealDateFrom = null, $managerRealDateTo = null): void
    {
        $query->when(request('id'), function (Builder $q) {
            $q->where("{$this->getTable()}.id", '=', request('id'));
        });

        $query->when(request('order_number'), function (Builder $q) {
            $q->where("{$this->getTable()}.order_number", '=', request('order_number'));
        });

        $query->when(request('created_at'), function (Builder $q) {
            $q->whereDate("{$this->getTable()}.created_at", '=', request('created_at'));
        });

        $query->when(request('delivery_mode'), function (Builder $q) {
            $q->where("{$this->getTable()}.delivery_mode", '=', request('delivery_mode'));
        });

        $query->when(request('source'), function (Builder $q) {
            $q->where("{$this->getTable()}.source", '=', request('source'));
        });

        $query->when(request('country_id'), function (Builder $q) {
            $q->where("{$this->getTable()}.country_id", '=', request('country_id'));
        });

        $query->when(request('shop_id'), function (Builder $q) {
            $q->where("{$this->getTable()}.shop_id", '=', request('shop_id'));
        });

        $query->when(request('user_id_operator'), function (Builder $q) {
            $q->where("{$this->getTable()}.user_id_operator", '=', request('user_id_operator'));
        });

        $query->when(request('user_id_manager'), function (Builder $q) {
            $q->where("{$this->getTable()}.user_id_manager", '=', request('user_id_manager'));
        });

        $query->when(request('user_id_driver'), function (Builder $q) {
            $q->where("{$this->getTable()}.user_id_driver", '=', request('user_id_driver'));
        });

        $query->when(request('client'), function (Builder $q) {
            $q->where(function ($query) {
                $client = request('client');
                $query->where("{$this->getTable()}.client_name", "LIKE", "%{$client}%")
                    ->orWhere("{$this->getTable()}.client_phone", "LIKE", "%{$client}%");
            });
        });

        $query->when($managerRealDateFrom && $managerRealDateTo, function (Builder $q) use ($managerRealDateFrom, $managerRealDateTo) {
            $q->whereBetween("{$this->getTable()}.manager_real_date", [$managerRealDateFrom, $managerRealDateTo]);
        });

        $query->when(request('status'), function (Builder $q) {
            $statuses = (array)request('status');
            $q->whereIn("{$this->getTable()}.status", $statuses);
        });

        $query->when(request('payment_status'), function (Builder $q) {
            $q->where("{$this->getTable()}.payment_status", '=', request('payment_status'));
        });

        $query->when(request('date_from') && request('date_to'), function (Builder $q) {
            $date_from = request('date_from') . 'T00:00:00';
            $date_to = request('date_to') . 'T00:00:00';
            $q->whereBetween("{$this->getTable()}.created_at", [$date_from, $date_to]);
        });

        $query->when(request('date_from_delivery') && request('date_to_delivery'), function (Builder $q) {
            $q->whereBetween("{$this->getTable()}.delivery_date", [request('date_from_delivery'), request('date_to_delivery')]);
        });
    }

    /**
     * @param int $shop_id
     * @return void
     */
    public function transferOrderToShop(int $shop_id)
    {
        $this->shop_id = $shop_id;
        $this->status = OrderStatus::InShop->value;
        $this->operator_real_date = now();
        $this->manager_deadline_date = now()->addMinutes(self::MANAGER_DEADLINE_IN_MINUTES);
        $this->user_id_operator = Auth::id();
        $this->update();
    }

    /**
     * @return void
     */
    public function transferOrderToDriver(): void
    {
        $this->status = OrderStatus::AtDriver->value;
        $this->manager_real_date = now();
        $this->driver_deadline_date = now()->addMinutes(self::DRIVER_DEADLINE_IN_MINUTES);
        $this->user_id_manager = Auth::id();
        $this->user_id_driver = Auth::id(); // После создания учетки для яндекса включить сюда конкретный id
        $this->update();
    }

    /**
     * @return void
     */
    public function orderDelivered(): void
    {
        $this->status = OrderStatus::Delivered->value;
        $this->driver_real_date = now();
        $this->user_id_driver = 6;
        $this->update();
    }

    /**
     * @return int
     */
    public static function generateOrderNumber(): int
    {
        $minOrderNumber = self::query()->min('order_number');

        $orderNumber = $minOrderNumber + 1;
        while (self::query()->where('order_number', $orderNumber)->exists()) {
            $orderNumber++;
        }

        return (int)$orderNumber;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function calculateTotalProcessingTime(): ?string
    {
        $totalTime = null;

        if ($this->driver_real_date && $this->created_at) {
            $driverRealDateTime = new DateTime($this->driver_real_date);
            $dateDateTime = new DateTime($this->created_at);

            $interval = $driverRealDateTime->diff($dateDateTime);

            $totalTime = $interval->format('%H:%I:%S');
        }

        return $totalTime;
    }
}
