<?php

namespace Mitrik\Shipping\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\Traits\UUID;

/**
 * Mitrik\Shipping\Models\ShippingBox
 *
 * @property int $id
 * @property string $uuid
 * @property float $length
 * @property float $width
 * @property float $height
 * @property float $max_weight
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|ShippingBox newModelQuery()
 * @method static Builder|ShippingBox newQuery()
 * @method static Builder|ShippingBox onlyTrashed()
 * @method static Builder|ShippingBox query()
 * @method static Builder|ShippingBox whereCreatedAt($value)
 * @method static Builder|ShippingBox whereDeletedAt($value)
 * @method static Builder|ShippingBox whereHeight($value)
 * @method static Builder|ShippingBox whereId($value)
 * @method static Builder|ShippingBox whereLength($value)
 * @method static Builder|ShippingBox whereMaxWeight($value)
 * @method static Builder|ShippingBox whereUpdatedAt($value)
 * @method static Builder|ShippingBox whereUuid($value)
 * @method static Builder|ShippingBox whereWidth($value)
 * @method static Builder|ShippingBox withTrashed()
 * @method static Builder|ShippingBox withoutTrashed()
 * @mixin \Eloquent
 */
class ShippingBox extends Model
{
    use HasFactory, UUID, SoftDeletes;

    /**
     * @var string[]
     */
    protected $appends = [
        'formatted'
    ];

    /**
     * @return float
     */
    public function volume(): float
    {
        return round($this->length * $this->width * $this->height, 2);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->length . 'x' . $this->width . 'x' . $this->height;
    }

    /**
     * @return BoxMetric
     */
    public function toBox(): BoxMetric
    {
        return new BoxMetric($this->length, $this->width, $this->height, 0, $this->max_weight);
    }

    /**
     * @return string
     */
    protected function getFormattedAttribute(): string
    {
        return $this->toString();
    }
}
