<?php

namespace Mitrik\Shipping\ServiceProviders\Box;

use Mitrik\Shipping\ServiceProviders\Measurement\Length;
use Mitrik\Shipping\ServiceProviders\Measurement\Weight;

/**
 * Base interface all boxes need to implement
 */
interface BoxInterface
{

    /**
     * @return float
     */
    public function length(): float;

    /**
     * @return float
     */
    public function width(): float;

    /**
     * @return float
     */
    public function height(): float;

    /**
     * @return float
     */
    public function weight(): float;

    /**
     * @return float
     */
    public function getLength(): float;

    /**
     * @param float $length
     */
    public function setLength(float $length): self;

    /**
     * @return float
     */
    public function getWidth(): float;

    /**
     * @param float $width
     */
    public function setWidth(float $width): self;

    /**
     * @return float
     */
    public function getHeight(): float;

    /**
     * @param float $height
     */
    public function setHeight(float $height): self;

    /**
     * @return float
     */
    public function getWeight(): float;

    /**
     * @param float $weight
     */
    public function setWeight(float $weight): self;

    /**
     * @return float
     */
    public function maxWeight(): float;

    /**
     * @param float $maxWeight
     */
    public function setMaxWeight(float $maxWeight): self;

    /**
     * @return float
     */
    public function volume(): float;

    /**
     * @return mixed
     */
    public function __toString();

    /**
     * @return Length
     */
    public function unitOfMeasurementSize(): Length;

    /**
     * @return Weight
     */
    public function unitOfMeasurementWeight(): Weight;

    /**
     * @param float|null $weight
     * @return mixed
     */
    public function isOverweight(float|null $weight = null): bool;
}
