<?php

namespace Mitrik\Shipping\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Mitrik\Shipping\Traits\UUID;

/**
 * Mitrik\Shipping\Models\ShippingOptionPrice
 *
 * @property int $id
 * @property string $uuid
 * @property int $shipping_service_id
 * @property int $shipping_option_id
 * @property int $shipping_box_id
 * @property float $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \Mitrik\Shipping\Models\ShippingBox $shippingBox
 * @method static Builder|ShippingOptionPrice newModelQuery()
 * @method static Builder|ShippingOptionPrice newQuery()
 * @method static Builder|ShippingOptionPrice onlyTrashed()
 * @method static Builder|ShippingOptionPrice query()
 * @method static Builder|ShippingOptionPrice whereCreatedAt($value)
 * @method static Builder|ShippingOptionPrice whereDeletedAt($value)
 * @method static Builder|ShippingOptionPrice whereId($value)
 * @method static Builder|ShippingOptionPrice wherePrice($value)
 * @method static Builder|ShippingOptionPrice whereShippingBoxId($value)
 * @method static Builder|ShippingOptionPrice whereShippingOptionId($value)
 * @method static Builder|ShippingOptionPrice whereShippingServiceId($value)
 * @method static Builder|ShippingOptionPrice whereUpdatedAt($value)
 * @method static Builder|ShippingOptionPrice whereUuid($value)
 * @method static Builder|ShippingOptionPrice withTrashed()
 * @method static Builder|ShippingOptionPrice withoutTrashed()
 * @mixin \Eloquent
 */
class ShippingOptionPrice extends Model
{
    use HasFactory, UUID, SoftDeletes;

    /**
     * @return BelongsTo
     */
    public function shippingBox(): BelongsTo
    {
        return $this->belongsTo(ShippingBox::class);
    }
}
