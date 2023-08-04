<?php

namespace App\Models;

use App\Enums\DeliveryMode;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $order_number
 * @property string|null $mobile_backend_callback_url
 * @property string $client_phone
 * @property string $client_name
 * @property int $country_id
 * @property string $address
 * @property string $latitude
 * @property string $longitude
 * @property string|null $entrance
 * @property string|null $floor
 * @property string|null $flat
 * @property float $order_price
 * @property float $payment_cash
 * @property float $payment_bonuses
 * @property PaymentStatus $payment_status
 * @property string|null $payment_url
 * @property string|null $comment_for_operator
 * @property string|null $operator_deadline_date
 * @property string|null $operator_real_date
 * @property int|null $user_id_operator
 * @property string|null $comment_for_manager
 * @property string|null $manager_deadline_date
 * @property string|null $manager_real_date
 * @property int|null $user_id_manager
 * @property string|null $comment_for_driver
 * @property string|null $driver_deadline_date
 * @property string|null $driver_real_date
 * @property int|null $user_id_driver
 * @property int|null $shop_id
 * @property OrderSource $source
 * @property DeliveryMode $delivery_mode
 * @property string|null $delivery_date
 * @property OrderStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Country $country
 * @property-read Shop|null $shop
 * @property-read User|null $operator
 * @property-read User|null $manager
 * @property-read User|null $driver
 * @property-read OrderItem|null $items
 * @property-read OrderDeliveryInYandex|null $deliveryInYandex
 * @property-read OrderLog|null $logs
 *
 * @mixin Builder
 */
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
     * @param array $data
     * @return void
     */
    public function scopeFilter(Builder $query, array $data): void
    {
        if (isset($data['id'])) {
            $query->where("{$this->getTable()}.id", '=', $data['id']);
        }

        if (isset($data['order_number'])) {
            $query->where("{$this->getTable()}.order_number", '=', $data['order_number']);
        }

        if (isset($data['created_at'])) {
            $query->whereDate("{$this->getTable()}.created_at", '=', $data['created_at']);
        }

        if (isset($data['delivery_mode'])) {
            $query->where("{$this->getTable()}.delivery_mode", '=', $data['delivery_mode']);
        }

        if (isset($data['source'])) {
            $query->where("{$this->getTable()}.source", '=', $data['source']);
        }

        if (isset($data['country_id'])) {
            $query->where("{$this->getTable()}.country_id", '=', $data['country_id']);
        }

        if (isset($data['shop_id'])) {
            $query->where("{$this->getTable()}.shop_id", '=', $data['shop_id']);
        }

        if (isset($data['user_id_operator'])) {
            $query->where("{$this->getTable()}.user_id_operator", '=', $data['user_id_operator']);
        }

        if (isset($data['user_id_manager'])) {
            $query->where("{$this->getTable()}.user_id_manager", '=', $data['user_id_manager']);
        }

        if (isset($data['user_id_driver'])) {
            $query->where("{$this->getTable()}.user_id_driver", '=', $data['user_id_driver']);
        }

        if (isset($data['client'])) {
            $client = $data['client'];
            $query->where(function ($q) use ($client) {
                $q->where("{$this->getTable()}.client_name", "LIKE", "%{$client}%")
                    ->orWhere("{$this->getTable()}.client_phone", "LIKE", "%{$client}%");
            });
        }

        if (isset($data['address'])) {
            $query->where("{$this->getTable()}.address", 'LIKE', "%{$data['address']}%");
        }

        if (isset($data['status'])) {
            $statuses = (array)$data['status'];
            $query->whereIn("{$this->getTable()}.status", $statuses);
        }

        if (isset($data['payment_status'])) {
            $query->where("{$this->getTable()}.payment_status", '=', $data['payment_status']);
        }

        if (isset($data['date_from']) && isset($data['date_to'])) {
            $date_from = $data['date_from'] . 'T00:00:00';
            $date_to = $data['date_to'] . 'T00:00:00';
            $query->whereBetween("{$this->getTable()}.created_at", [$date_from, $date_to]);
        }

        if (isset($data['date_from_delivery']) && isset($data['date_to_delivery'])) {
            $query->whereBetween("{$this->getTable()}.delivery_date", [$data['date_from_delivery'], $data['date_to_delivery']]);
        }

        if (isset($data['manager_real_date_from']) && isset($data['manager_real_date_to'])) {
            $query->whereBetween("{$this->getTable()}.manager_real_date", [$data['manager_real_date_from'], $data['manager_real_date_to']]);
        }
    }

    /**
     * @param int $shop_id
     * @return void
     */
    public function transferOrderToShop(int $shop_id): void
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
     * @throws Exception
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

    /**
     * @param string $reasonCancel
     * @return void
     */
    public function statusChangeToCanceledForPaidOrder(string $reasonCancel): void
    {
        $commentForOperator = trim($this->comment_for_operator); // удаление пробелов из начала и конца строки

        if (strlen($commentForOperator) === 0) { // если комментария не было
            $commentForOperator .= '';
        } else if (str_ends_with($commentForOperator, '.')) { // если есть точка в конце строки, то добавляем пробел
            $commentForOperator .= ' ';
        } else {
            $commentForOperator .= '. '; // иначе добавляем точки в конце строки
        }

        $commentForOperator .= 'Причина отмены: ' . $reasonCancel;

        $this->comment_for_operator = $commentForOperator;
        $this->status = OrderStatus::Canceled->value;
        $this->update();
    }
}
