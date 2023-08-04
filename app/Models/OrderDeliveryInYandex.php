<?php

namespace App\Models;

use App\Events\OrderDeliveryInYandexEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property string $yandex_id
 * @property int $shop_id
 * @property string $shop_address
 * @property string $shop_latitude
 * @property string $shop_longitude
 * @property string $client_address
 * @property string $client_latitude
 * @property string $client_longitude
 * @property string|null $tariff
 * @property float $offer_price
 * @property float $final_price
 * @property string|null $driver_phone
 * @property string|null $driver_phone_ext
 * @property int $user_id
 * @property string|null $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Order $order
 * @property-read Shop $shops
 * @property-read User $user
 *
 * @mixin Builder
 */
class OrderDeliveryInYandex extends Model
{
    use HasFactory;

    protected $table = 'order_delivery_in_yandex';

    protected $fillable = [
        'yandex_id',
        'shop_id',
        'shop_address',
        'shop_latitude',
        'shop_longitude',
        'client_address',
        'client_latitude',
        'client_longitude',
        'tariff',
        'status',
        'user_id',
    ];

    const YANDEX_STATUS_ACCEPTED = 'accepted';
    const YANDEX_STATUS_CANCELLED = 'cancelled';
    const YANDEX_STATUS_CANCELLED_BY_TAXI = 'cancelled_by_taxi';
    const YANDEX_STATUS_CANCELLED_WITH_ITEMS_ON_HANDS = 'cancelled_with_items_on_hands';
    const YANDEX_STATUS_CANCELLED_WITH_PAYMENT = 'cancelled_with_payment';
    const YANDEX_STATUS_DELIVERED = 'delivered';
    const YANDEX_STATUS_DELIVERED_FINISH = 'delivered_finish';
    const YANDEX_STATUS_DELIVERY_ARRIVED = 'delivery_arrived';
    const YANDEX_STATUS_ESTIMATING = 'estimating';
    const YANDEX_STATUS_ESTIMATING_FAILED = 'estimating_failed';
    const YANDEX_STATUS_FAILED = 'failed';
    const YANDEX_STATUS_NEW = 'new';
    const YANDEX_STATUS_PERFORMER_DRAFT = 'performer_draft';
    const YANDEX_STATUS_PERFORMER_FOUND = 'performer_found';
    const YANDEX_STATUS_PERFORMER_LOOKUP = 'performer_lookup';
    const YANDEX_STATUS_PERFORMER_NOT_FOUND = 'performer_not_found';
    const YANDEX_STATUS_PICKUP_ARRIVED = 'pickup_arrived';
    const YANDEX_STATUS_PICKUPED = 'pickuped';
    const YANDEX_STATUS_READY_FOR_APPROVAL = 'ready_for_approval';
    const YANDEX_STATUS_READY_FOR_DELIVERY_CONFIRMATION = 'ready_for_delivery_confirmation';
    const YANDEX_STATUS_READY_FOR_PICKUP_CONFIRMATION = 'ready_for_pickup_confirmation';
    const YANDEX_STATUS_READY_FOR_RETURN_CONFIRMATION = 'ready_for_return_confirmation';
    const YANDEX_STATUS_RETURN_ARRIVED = 'return_arrived';
    const YANDEX_STATUS_RETURNED = 'returned';
    const YANDEX_STATUS_RETURNED_FINISH = 'returned_finish';
    const YANDEX_STATUS_RETURNING = 'returning';

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($orderDeliveryInYandex) {
            event(new OrderDeliveryInYandexEvent($orderDeliveryInYandex->order_id));
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Статусы Яндекс заказов из документации https://yandex.ru/dev/logistics/api/about/claims-status.html
     *
     * @return string[]
     */
    public static function getYandexStatuses(): array
    {
        return [
            self::YANDEX_STATUS_ACCEPTED => 'Заявка подтверждена клиентом.',
            self::YANDEX_STATUS_CANCELLED => 'Заказ был отменен клиентом бесплатно.',
            self::YANDEX_STATUS_CANCELLED_BY_TAXI => 'Водитель отменил заказ (до получения груза).',
            self::YANDEX_STATUS_CANCELLED_WITH_ITEMS_ON_HANDS => 'Клиент платно отменил заявку без необходимости возврата груза (заявка была создана с флагом optional_return).',
            self::YANDEX_STATUS_CANCELLED_WITH_PAYMENT => 'Заказ был отменен клиентом платно (водитель уже приехал).',
            self::YANDEX_STATUS_DELIVERED => 'Водитель успешно доставил груз.',
            self::YANDEX_STATUS_DELIVERED_FINISH => 'Заказ завершен.',
            self::YANDEX_STATUS_DELIVERY_ARRIVED => 'Водитель приехал в точку Б.',
            self::YANDEX_STATUS_ESTIMATING => 'Идет процесс оценки заявки (подбор типа автомобиля по параметрам груза и расчет стоимости).',
            self::YANDEX_STATUS_ESTIMATING_FAILED => 'Не удалось оценить заявку.',
            self::YANDEX_STATUS_FAILED => 'При выполнение заказа произошла ошибка, дальнейшее выполнение невозможно.',
            self::YANDEX_STATUS_NEW => 'Новая заявка.',
            self::YANDEX_STATUS_PERFORMER_DRAFT => 'Идет поиск водителя.',
            self::YANDEX_STATUS_PERFORMER_FOUND => 'Водитель найден и едет в точку А.',
            self::YANDEX_STATUS_PERFORMER_LOOKUP => 'Заявка взята в обработку. Промежуточный статус перед созданием заказа.',
            self::YANDEX_STATUS_PERFORMER_NOT_FOUND => 'Не удалось найти водителя. Можно попробовать снова через некоторое время.',
            self::YANDEX_STATUS_PICKUP_ARRIVED => 'Водитель приехал в точку А.',
            self::YANDEX_STATUS_PICKUPED => 'Водитель успешно забрал груз.',
            self::YANDEX_STATUS_READY_FOR_APPROVAL => 'Заявка успешно оценена и ожидает подтверждения от клиента.',
            self::YANDEX_STATUS_READY_FOR_DELIVERY_CONFIRMATION => 'Водитель ждет, когда получатель назовет ему код подтверждения.',
            self::YANDEX_STATUS_READY_FOR_PICKUP_CONFIRMATION => 'Водитель ждет, когда отправитель назовет ему код подтверждения.',
            self::YANDEX_STATUS_READY_FOR_RETURN_CONFIRMATION => 'Водитель в точке возврата ожидает, когда ему назовут код подтверждения.',
            self::YANDEX_STATUS_RETURN_ARRIVED => 'Водитель приехал в точку возврата.',
            self::YANDEX_STATUS_RETURNED => 'Водитель успешно вернул груз.',
            self::YANDEX_STATUS_RETURNED_FINISH => 'Заказ завершен (вернул груз).',
            self::YANDEX_STATUS_RETURNING => 'Водителю пришлось вернуть груз и он едет в точку возврата.',
        ];
    }

    /**
     * статусы при которых можно отменить заказ
     *
     * @return string[]
     */
    public static function getYandexStatusesThatCanBeCanceled(): array
    {
        return [
            self::YANDEX_STATUS_ACCEPTED,
            self::YANDEX_STATUS_ESTIMATING,
            self::YANDEX_STATUS_ESTIMATING_FAILED,
            self::YANDEX_STATUS_NEW,
            self::YANDEX_STATUS_PERFORMER_DRAFT,
            self::YANDEX_STATUS_PERFORMER_FOUND,
            self::YANDEX_STATUS_PERFORMER_LOOKUP,
            self::YANDEX_STATUS_PERFORMER_NOT_FOUND,
            self::YANDEX_STATUS_PICKUP_ARRIVED,
            self::YANDEX_STATUS_READY_FOR_APPROVAL
        ];
    }
}
