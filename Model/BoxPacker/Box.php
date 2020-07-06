<?php

namespace Bleez\Correios\Model\BoxPacker;

class Box implements \DVDoug\BoxPacker\Box {


    /**
     * @var string
     */
    private $reference;

    /**
     * @var int
     */
    private $outerWidth;

    /**
     * @var int
     */
    private $outerLength;

    /**
     * @var int
     */
    private $outerDepth;

    /**
     * @var int
     */
    private $emptyWeight;

    /**
     * @var int
     */
    private $innerWidth;

    /**
     * @var int
     */
    private $innerLength;

    /**
     * @var int
     */
    private $innerDepth;

    /**
     * @var int
     */
    private $maxWeight;

    /**
     * @var int
     */
    private $innerVolume;

    /**
     * TestBox constructor.
     *
     * @param string $reference
     * @param int $outerWidth
     * @param int $outerLength
     * @param int $outerDepth
     * @param int $emptyWeight
     * @param int $innerWidth
     * @param int $innerLength
     * @param int $innerDepth
     * @param int $maxWeight
     */
    public function __construct(
        $reference,
        $outerWidth,
        $outerLength,
        $outerDepth,
        $emptyWeight,
        $innerWidth,
        $innerLength,
        $innerDepth,
        $maxWeight
    ) {
        $this->reference = $reference;
        $this->outerWidth = $outerWidth;
        $this->outerLength = $outerLength;
        $this->outerDepth = $outerDepth;
        $this->emptyWeight = $emptyWeight;
        $this->innerWidth = $innerWidth;
        $this->innerLength = $innerLength;
        $this->innerDepth = $innerDepth;
        $this->maxWeight = $maxWeight;
        $this->innerVolume = $this->innerWidth * $this->innerLength * $this->innerDepth;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return int
     */
    public function getOuterWidth(): int
    {
        return $this->outerWidth;
    }

    /**
     * @return int
     */
    public function getOuterLength(): int
    {
        return $this->outerLength;
    }

    /**
     * @return int
     */
    public function getOuterDepth(): int
    {
        return $this->outerDepth;
    }

    /**
     * @return int
     */
    public function getEmptyWeight(): int
    {
        return $this->emptyWeight;
    }

    /**
     * @return int
     */
    public function getInnerWidth(): int
    {
        return $this->innerWidth;
    }

    /**
     * @return int
     */
    public function getInnerLength(): int
    {
        return $this->innerLength;
    }

    /**
     * @return int
     */
    public function getInnerDepth(): int
    {
        return $this->innerDepth;
    }

    /**
     * @return int
     */
    public function getMaxWeight(): int
    {
        return $this->maxWeight;
    }

    /**
     * @inheritDoc
     */
    public function getInnerVolume(): int
    {
        return $this->innerVolume;
    }
}
