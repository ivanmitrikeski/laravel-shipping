<?php

namespace Mitrik\Shipping\Traits;

use Illuminate\Support\Str;

/**
 *
 */
trait UUID {
    /**
     * @return void
     */
    protected static function bootUUID(): void
    {
        static::creating(
            function ($model) {
                $model->uuid = (string) Str::uuid();
            }
        );
    }

    /**
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @param array $values
     * @return array
     */
    protected function getArrayableItems(array $values): array
    {
        $this->hidden[] = 'id';
        $this->hidden[] = 'laravel_through_key';
        $this->hidden[] = 'deleted_at';

        return parent::getArrayableItems($values);
    }

}
