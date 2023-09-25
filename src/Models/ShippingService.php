<?php

namespace Mitrik\Shipping\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Mitrik\Shipping\ServiceProviders\ServiceProvider;
use Mitrik\Shipping\Traits\UUID;

/**
 * Mitrik\Shipping\Models\ShippingService
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $service_provider_class
 * @property bool $is_enabled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, \Mitrik\Shipping\Models\ShippingOption> $shippingOptions
 * @property-read int|null $shipping_options_count
 * @method static Builder|ShippingService enabled()
 * @method static Builder|ShippingService newModelQuery()
 * @method static Builder|ShippingService newQuery()
 * @method static Builder|ShippingService onlyTrashed()
 * @method static Builder|ShippingService query()
 * @method static Builder|ShippingService whereCreatedAt($value)
 * @method static Builder|ShippingService whereDeletedAt($value)
 * @method static Builder|ShippingService whereId($value)
 * @method static Builder|ShippingService whereIsEnabled($value)
 * @method static Builder|ShippingService whereName($value)
 * @method static Builder|ShippingService whereServiceProviderClass($value)
 * @method static Builder|ShippingService whereUpdatedAt($value)
 * @method static Builder|ShippingService whereUuid($value)
 * @method static Builder|ShippingService withTrashed()
 * @method static Builder|ShippingService withoutTrashed()
 * @mixin \Eloquent
 */
class ShippingService extends Model
{
    use HasFactory, UUID, SoftDeletes;

    /**
     * @var string[]
     */
    protected $casts = [
        'is_enabled' => 'boolean'
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'service_provider_class',
    ];

    /**
     * @return HasMany
     */
    public function shippingOptions(): HasMany
    {
        return $this->hasMany(ShippingOption::class);
    }

    /**
     * @return array
     */
    public function credentialKeys(): array
    {
        return $this->service_provider_class::credentialKeys();
    }

    /**
     * @return ServiceProvider
     * @throws Exception
     */
    public function instance(): ServiceProvider
    {
        if (!class_exists($this->service_provider_class)) {
            throw new Exception($this->service_provider_class . ' does not exist.');
        }

        $credentialsClass = $this->service_provider_class . 'Credentials';
        if (!class_exists($credentialsClass)) {
            throw new Exception($credentialsClass . ' does not exist.');
        }

        $credentialKeys = $this->service_provider_class::credentialKeys();
        $credentials = [];

        foreach ($credentialKeys as $credentialKey) {
            $credentials[] = env($credentialKey, '');
        }

        $credentials[] = env('SHIPPING_SANDBOX', false);

        $modelCredentials = new $credentialsClass(... $credentials);

        return new $this->service_provider_class($modelCredentials);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }
}
