<?php

namespace Mitrik\Shipping\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Mitrik\Shipping\Traits\UUID;

/**
 * Mitrik\Shipping\Models\ShippingOption
 *
 * @property int $id
 * @property string $uuid
 * @property int $shipping_service_id
 * @property string $code
 * @property string $name
 * @property bool $is_enabled
 * @property bool $is_internal
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, \Mitrik\Shipping\Models\ShippingOptionPrice> $shippingOptionPrices
 * @property-read int|null $shipping_option_prices_count
 * @property-read \Mitrik\Shipping\Models\ShippingService $shippingService
 * @method static Builder|ShippingOption newModelQuery()
 * @method static Builder|ShippingOption newQuery()
 * @method static Builder|ShippingOption onlyTrashed()
 * @method static Builder|ShippingOption query()
 * @method static Builder|ShippingOption whereCode($value)
 * @method static Builder|ShippingOption whereCreatedAt($value)
 * @method static Builder|ShippingOption whereDeletedAt($value)
 * @method static Builder|ShippingOption whereId($value)
 * @method static Builder|ShippingOption whereIsEnabled($value)
 * @method static Builder|ShippingOption whereIsInternal($value)
 * @method static Builder|ShippingOption whereName($value)
 * @method static Builder|ShippingOption whereShippingServiceId($value)
 * @method static Builder|ShippingOption whereUpdatedAt($value)
 * @method static Builder|ShippingOption whereUuid($value)
 * @method static Builder|ShippingOption withTrashed()
 * @method static Builder|ShippingOption withoutTrashed()
 * @mixin \Eloquent
 */
class ShippingOption extends Model
{
    use HasFactory, UUID, SoftDeletes;

    /**
     * @var string[]
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'is_internal' => 'boolean'
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'shipping_service_id'
    ];

    /**
     * @return HasMany
     */
    public function shippingOptionPrices(): HasMany
    {
        return $this->hasMany(ShippingOptionPrice::class);
    }

    /**
     * @return BelongsTo
     */
    public function shippingService(): BelongsTo
    {
        return $this->belongsTo(ShippingService::class);
    }
}
