<?php
declare(strict_types=1);

namespace fund\Service;

use DateTimeImmutable;
use fund\Dto\CostDto;
use fund\Dto\FundDto;
use fund\Dto\StructureDto;
use fund\Enum\CostPeriodIdEnum;
use fund\Helper\DateHelper;
use fund\Helper\DbHelper;

class FundService
{
    private DbHelper $dbHelper;
    private DateHelper $dateHelper;

    public function __construct(DbHelper $dbHelper)
    {
        $this->dbHelper = $dbHelper;
        $this->dateHelper = new DateHelper();
    }

    /**
     * @param int[] $ufCodeIds
     *
     * @return FundDto[]
     */
    public function getList(array $ufCodeIds = [], bool $withCosts = true, bool $withStructure = true): array
    {
        $fundDtos = [];
        $funds = $this->dbHelper->getFundList($ufCodeIds);
        foreach ($funds as $fund) {
            $costDtos = $structureDtos = [];
            if (true === $withCosts) {
                $costDtos = $this->getCostDtoByFundIdAndDate((int) $fund['id']);
            }
            if (true === $withStructure) {
                $structureDtos = $this->getStructureByFundId((int) $fund['id']);
            }

            $fundDtos[] = $this->createFundDto($fund, $costDtos, $structureDtos);
        }
        return $fundDtos;
    }

    public function getFundById(int $id, bool $withCosts = true, bool $withStructure = true): ?FundDto
    {
        $fund = $this->dbHelper->getFundById($id);
        if (null === $fund) {
            return $fund;
        }

        $costDtos = $structureDtos = [];
        if (true === $withCosts) {
            $costDtos = $this->getCostDtoByFundIdAndDate($id);
        }
        if (true === $withStructure) {
            $structureDtos = $this->getStructureByFundId($id);
        }

        return $this->createFundDto($fund, $costDtos, $structureDtos);
    }

    /**
     * @return CostDto[]
     */
    public function getCostByFundIdAndPeriod(
        int $fundId,
        DateTimeImmutable $dateFrom,
        DateTimeImmutable $dateTill
    ): array {
        $costDtos = [];
        $costs = $this->dbHelper->getCostByFundIdAndPeriod($fundId, $dateFrom, $dateTill);
        foreach ($costs as $cost) {
            $costDtos[] = $this->createCostDto($cost, null, null);
        }
        return $costDtos;
    }

    /**
     * @return CostDto[]
     */
    public function getCostByFundIdAndPeriodId(
        int $fundId,
        int $periodId,
        DateTimeImmutable $dateTill
    ): array {
        $costDtos = [];
        $dateFrom = $this->dateHelper->getDateTillByPeriodId($periodId, $dateTill);
        $costs = $this->dbHelper->getCostByFundIdAndPeriod($fundId, $dateFrom, $dateTill);
        foreach ($costs as $cost) {
            $costDtos[] = $this->createCostDto($cost, null, null);
        }
        return $costDtos;
    }

    /**
     * @return StructureDto[]
     */
    public function getStructureByFundId(int $fundId): array
    {
        $structureDtos = [];

        $res = $this->dbHelper->getStructureByFundId($fundId);
        $sumValue = 0;
        foreach ($res as $structure) {
            $sumValue += $structure['value'];
        }

        foreach ($res as $structure) {
            $structureDtos[] = $this->createStructureDto($structure, $sumValue);
        }
        return $structureDtos;
    }

    /**
     * @return DateTimeImmutable[]
     */
    public function getDatesForCost(): array
    {
        $dateStart = $this->getLastWorkDate(new DateTimeImmutable());
        return $this->dateHelper->getDatesForCost($dateStart);
    }

    /**
     * @return CostDto[]
     */
    private function getCostDtoByFundIdAndDate(int $fundId): array
    {
        $costDtos = [];
        $shareCostValue = null;
        foreach ($this->getDatesForCost() as $periodKey => $date) {
            $costDtos[$periodKey] = $this->getCostByFundIdAndDate($fundId, $date, $shareCostValue, $periodKey);
            $shareCostValue = $this->getShareCostValue($periodKey, $costDtos[$periodKey], $shareCostValue);
        }
        return $costDtos;
    }

    private function getShareCostValue(int $periodKey, CostDto $costDto, ?float $shareCostValue): ?float
    {
        if (CostPeriodIdEnum::LAST_WORK_PERIOD === $periodKey) {
            return $costDto->getShareCost();
        }
        return $shareCostValue;
    }

    private function getLastWorkDate(DateTimeImmutable $date): DateTimeImmutable
    {
        $date = $this->dateHelper->getLastWorkDataByDate($date);
        if (true === $this->dbHelper->hasCostByDate($date)) {
            return $date;
        }
        $newDateTime = $date->modify('-1 month');
        return $this->getLastWorkDate($newDateTime);
    }

    private function getCostByFundIdAndDate(int $fundId, DateTimeImmutable $date, ?float $shareCostValue, ?int $periodId): CostDto
    {
        $cost = $this->dbHelper->getCostByFundIdAndDate($fundId, $date);
        return $this->createCostDto($cost, $shareCostValue, $periodId);
    }

    /**
     * @param mixed[] $fund
     * @param CostDto[] $costDtos
     */
    private function createFundDto(array $fund, array $costDtos, array $structureDtos): FundDto
    {
        return new FundDto(
            (int) $fund['id'],
            $fund['name'],
            $fund['description'],
            (int) $fund['active_status'],
            $costDtos,
            $structureDtos
        );
    }

    /**
     * @param mixed[]|null $cost
     */
    private function createCostDto(?array $cost, ?float $shareCostValue, ?int $periodTypeId): CostDto
    {
        $shareCost = $cost['share_cost'] ?? null;
        $accetsCost = $cost['accets_cost'] ?? null;
        $costDate = $cost['date'] ? new DateTimeImmutable($cost['date']->format('Y-m-d')) : null;
        $percent = null;

        if (null !== $shareCostValue && null !== $shareCost) {
            $percent = ($shareCostValue - $shareCost ) / $shareCost * 100;
        }
        return new CostDto(
            (float) $shareCost,
            (float) $accetsCost,
            $costDate,
            $percent,
            $periodTypeId
        );
    }

    /**
     * @param mixed[]|null $structure
     */
    private function createStructureDto(?array $structure, float $sumValue): StructureDto
    {
        $value = $structure['value'] ?? null;
        $typeId = $structure['type_id'] ?? null;
        $percent = null;

        if (null != $sumValue) {
            $percent = abs($value / $sumValue * 100);
        }
        return new StructureDto(
            (float) $value,
            (int) $typeId,
            $sumValue,
            $percent
        );
    }
}