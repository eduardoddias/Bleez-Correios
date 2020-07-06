<?php

namespace Bleez\Correios\Model\BoxPacker;

/**
 * Class Item
 * @package Bleez\Correios\Model\BoxPacker
 */
class Item implements \DVDoug\BoxPacker\Item {

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var int
     */
    private $weight;

    /**
     * @var int
     */
    private $keepFlat;

    /**
     * @var int
     */
    private $volume;

    /**
     * @var int
     */
    private $originPostcode;

    /**
     * TestItem constructor.
     *
     * @param string $description
     * @param int $width
     * @param int $length
     * @param int $depth
     * @param int $weight
     * @param int $keepFlat
     * @param int $originPostcode
     */
    public function __construct($description, $width, $length, $depth, $weight, $keepFlat, $originPostcode)
    {
        $this->description = $description;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
        $this->weight = $weight;
        $this->keepFlat = $keepFlat;
        $this->originPostcode = $originPostcode;

        $this->volume = $this->width * $this->length * $this->depth;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @return int
     */
    public function getKeepFlat(): bool
    {
        return $this->keepFlat;
    }

    /**
     * @return int
     */
    public function getOriginPostcode(): int
    {
        return $this->originPostcode;
    }
}
