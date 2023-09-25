<?php

namespace Mitrik\Shipping\ServiceProviders\Box;

use Mitrik\Shipping\ServiceProviders\Measurement\Length;
use Mitrik\Shipping\ServiceProviders\Measurement\Weight;

/**
 * Box specifications
 */
class BoxMetric extends Box
{
    /**
     * @return Length
     */
    public function unitOfMeasurementSize(): Length
    {
        return Length::CM;
    }

    /**
     * @return Weight
     */
    public function unitOfMeasurementWeight(): Weight
    {
        return Weight::KG;
    }
}
