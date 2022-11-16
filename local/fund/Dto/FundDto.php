<?php
declare(strict_types=1);

namespace fund\Dto;

class FundDto
{
    private int $fundId;
    private string $name;
    private string $description;
    private int $status;
    /**
     * @var CostDto[]
     */
    private array $costs;

    public function __construct(
        int $fundId,
        string $name,
        string $description,
        int $status,
        array $costs
    ) {
        $this->fundId = $fundId;
        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
        $this->costs = $costs;
    }

    public function getFundId(): int
    {
        return $this->fundId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return CostDto[]
     */
    public function getCosts(): array
    {
        return $this->costs;
    }
}
