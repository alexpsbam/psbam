<?php
declare(strict_types=1);

namespace fund\Dto;

use DateTimeImmutable;

class CostDto
{
    private ?float $shareCost;
    private ?float $accetsCost;
    private ?DateTimeImmutable $date;
    private ?float $percent;

    public function __construct(
        ?float $shareCost,
        ?float $accetsCost,
        ?DateTimeImmutable $date,
        ?float $percent
    ) {
        $this->shareCost = $shareCost;
        $this->accetsCost = $accetsCost;
        $this->date = $date;
        $this->percent = $percent;
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
}