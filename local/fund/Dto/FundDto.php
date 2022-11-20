<?php
declare(strict_types=1);

namespace fund\Dto;

use JsonSerializable;

class FundDto implements JsonSerializable
{
    private int $fundId;
    private string $name;
    private string $description;
    private int $status;
    /**
     * @var CostDto[]
     */
    private array $costs;

    /**
     * @var StructureDto[]
     */
    private array $structures;

    public function __construct(
        int $fundId,
        string $name,
        string $description,
        int $status,
        array $costs,
        array $structures
    ) {
        $this->fundId = $fundId;
        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
        $this->costs = $costs;
        $this->structures = $structures;
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

    /**
     * @return StructureDto[]
     */
    public function getStructures(): array
    {
        return $this->structures;
    }

    public function jsonSerialize()
    {
        $costs = $structures = [];
        foreach ($this->getCosts() as $costDto) {
            $costs[] = $costDto->jsonSerialize();
        }

        foreach ($this->getStructures() as $structureDto) {
            $structures[] = $structureDto->jsonSerialize();
        }
        return [
            'fundId' => $this->getFundId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'costs' => $costs,
            'structures' => $structures,
        ];
    }
}
