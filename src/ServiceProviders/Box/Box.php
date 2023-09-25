<?php

namespace Mitrik\Shipping\ServiceProviders\Box;

use Stringable;

/**
 * Box specifications
 */
abstract class Box implements BoxInterface, Stringable
{
    /**
     * Box length
     *
     * @var float
     */
    private float $length;

    /**
     * Box width
     *
     * @var float
     */
    private float $width;

    /**
     * Box height
     *
     * @var float
     */
    private float $height;

    /**
     * Box weight
     *
     * @var float
     */
    private float $weight;

    /**
     * Maximum box weight
     *
     * @var float
     */
    private float $maxWeight;

    /**
     * @param float $length
     * @param float $width
     * @param float $height
     * @param float $weight
     * @param float $maxWeight
     */
    public function __construct(float $length, float $width, float $height, float $weight, float $maxWeight = 0)
    {
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->weight = $weight;
        $this->maxWeight = $maxWeight;
    }

    /**
     * @return float
     */
    public function length(): float
    {
        return $this->length;
    }

    /**
     * @return float
     */
    public function width(): float
    {
        return $this->width;
    }

    /**
     * @return float
     */
    public function height(): float
    {
        return $this->height;
    }

    /**
     * @return float
     */
    public function weight(): float
    {
        return $this->weight;
    }

    /**
     * @return float
     */
    public function getLength(): float
    {
        return $this->length;
    }

    /**
     * @param float $length
     * @return Box
     */
    public function setLength(float $length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * @param float $width
     * @return Box
     */
    public function setWidth(float $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return float
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * @param float $height
     * @return Box
     */
    public function setHeight(float $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     * @return Box
     */
    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return float
     */
    public function maxWeight(): float
    {
        return $this->maxWeight;
    }

    /**
     * @param float $maxWeight
     * @return Box
     */
    public function setMaxWeight(float $maxWeight): self
    {
        $this->maxWeight = $maxWeight;

        return $this;
    }

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
    public function __toString()
    {
        return $this->length . 'x' . $this->width . 'x' . $this->height;
    }

    /**
     * @param float|null $weight
     * @return bool
     */
    public function isOverweight(float|null $weight = null): bool
    {
        if ($this->maxWeight() === 0.00) {
            return false;
        }

        if ($weight !== null) {
            return $weight > $this->maxWeight();
        }

        return $this->weight() > $this->maxWeight();
    }

}
