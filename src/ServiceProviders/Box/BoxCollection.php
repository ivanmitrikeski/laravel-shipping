<?php

namespace Mitrik\Shipping\ServiceProviders\Box;

use Illuminate\Support\Collection;
use Mitrik\Shipping\ServiceProviders\Measurement\Weight;

class BoxCollection extends Collection
{
    /**
     * @return float
     */
    public function weight(): float
    {
        /** @var BoxInterface $box */
        return (float) $this->sum(function (BoxInterface $box) {
            return $box->weight();
        });
    }

    /**
     * @return Weight
     */
    public function unitOfMeasurementWeight(): Weight
    {
        if ($this->count() === 0) {
            return Weight::KG;
        }

        /** @var BoxInterface $box */
        $box = $this->first();

        return $box->unitOfMeasurementWeight();
    }

}
