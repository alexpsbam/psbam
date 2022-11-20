<?php
declare(strict_types=1);

namespace fund\Dto;

use DateTimeImmutable;
use JsonSerializable;

class CostDto implements JsonSerializable
{
    private ?float $shareCost;
    private ?float $accetsCost;
    private ?DateTimeImmutable $date;
    private ?float $percent;
    private ?int $periodTypeId;

    public function __construct(
        ?float $shareCost,
        ?float $accetsCost,
        ?DateTimeImmutable $date,
        ?float $percent,
        ?int $periodTypeId = null
    ) {
        $this->shareCost = $shareCost;
        $this->accetsCost = $accetsCost;
        $this->date = $date;
        $this->percent = $percent;
        $this->periodTypeId = $periodTypeId;
    }

    public function getShareCost(): ?float
    {
        return $this->shareCost;
    }

    public function getAccetsCost(): ?float
    {
        return $this->accetsCost;
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function getPercent(): ?float
    {
        return $this->percent;
    }

    public function getPeriodTypeId(): ?float
    {
        return $this->periodTypeId;
    }

    public function jsonSerialize()
    {
        return [
            'shareCost' => $this->getShareCost(),
            'accetsCost' => $this->getAccetsCost(),
            'date' => $this->getDate() ? $this->getDate()->format('Y-m-d') : null,
            'percent' => $this->getPercent(),
            'periodTypeId' => $this->getPeriodTypeId(),
        ];
    }
}
