<?php
declare(strict_types=1);

namespace fund\Dto;

use JsonSerializable;

class StructureDto implements JsonSerializable
{
    private ?string $title;
    private ?float $value;
    private int $typeId;
    private float $sumValue;
    private ?float $percent;

    public function __construct(?string $title, ?float $value, int $typeId, float $sumValue, ?float $percent = null)
    {
        $this->title = $title;
        $this->value = $value;
        $this->typeId = $typeId;
        $this->sumValue = $sumValue;
        $this->percent = $percent;
    }
    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }

    public function getSumValue(): float
    {
        return $this->sumValue;
    }

    public function getPercent(): ?float
    {
        return $this->percent;
    }

    public function jsonSerialize()
    {
        return [
                'title' => $this->getTitle(),
                'value' => $this->getValue(),
                'typeId' => $this->getTypeId(),
                'sumValue' => $this->getSumValue(),
                'percent' => $this->getPercent(),
        ];
    }
}