<?php
declare(strict_types=1);

namespace fund\Service;

use DateTimeImmutable;
use fund\Dto\CostDto;
use fund\Dto\FundDto;
use fund\Enum\CostPeriodIdEnum;
use fund\Enum\LastWorkDaysEnum;
use fund\Helper\DbHelper;

class FundService
{
    private DbHelper $dbHelper;

    public function __construct(DbHelper $dbHelper)
    {
        $this->dbHelper = $dbHelper;
    }

    public function getList(): array
    {
        $fundDtos = [];
        $funds = $this->dbHelper->getFundList();
        foreach ($funds as $fund) {
            $shareCostValue = null;
            foreach ($this->getDatesForCostByDateStart() as $periodKey => $date) {
                $costDtos[$periodKey] = $this->findCostByFundIdAndDate((int) $fund['id'], $date, $shareCostValue);
                if (CostPeriodIdEnum::LAST_WORK_PERIOD === $periodKey) {
                    $shareCostValue = $costDtos[$periodKey]->getShareCost();
                }
            }
            $fundDtos[] = $this->createFundDto($fund, $costDtos);
        }
        return $fundDtos;
    }

    public function getFundById(int $id): ?FundDto
    {
        $fund = $this->dbHelper->getFundById($id);
        if (null === $fund) {
            return $fund;
        }
        $shareCostValue = null;
        foreach ($this->getDatesForCostByDateStart() as $periodKey => $date) {
            $costDtos[$periodKey] = $this->findCostByFundIdAndDate((int) $fund['id'], $date, $shareCostValue);
            if (CostPeriodIdEnum::LAST_WORK_PERIOD === $periodKey) {
                $shareCostValue = $costDtos[$periodKey]->getShareCost();
            }
        }
        return $this->createFundDto($fund, $costDtos);
    }

    private function createFundDto(array $fund, array $costDtos): FundDto
    {
        return new FundDto(
            (int) $fund['id'],
            $fund['name'],
            $fund['description'],
            (int) $fund['active_status'],
            $costDtos
        );
    }

    private function getLastWorkData(DateTimeImmutable $date): DateTimeImmutable
    {
        $monthLastValue = (int) $date->format('m');
        $yearLastValue = (int) $date->format('Y');
        $dateString = LastWorkDaysEnum::LAST_WORK_DAYS[$yearLastValue][$monthLastValue];
        if (true === $this->dbHelper->hasCostByDate(new DateTimeImmutable($dateString))) {
            return new DateTimeImmutable($dateString);
        }
        $newDateTime = $date->modify('-1 month');
        return $this->getLastWorkData($newDateTime);

    }

    public function getDatesForCostByDateStart(): array
    {
        $dateStart = $this->getLastWorkData(new DateTimeImmutable());
        $dates[CostPeriodIdEnum::LAST_DAY] = new DateTimeImmutable();
        $dates[CostPeriodIdEnum::LAST_WORK_PERIOD] = $dateStart;
        foreach (CostPeriodIdEnum::PERIODS as $key => $interval) {
            $dates[$key] = $dateStart->modify($interval);
        }
        return $dates;
    }

    private function findCostByFundIdAndDate(int $fundId, DateTimeImmutable $date, ?float $shareCostValue): CostDto
    {
        $cost = $this->dbHelper->findCostByFundIdAndDate($fundId, $date);
        return $this->createCostDto($cost, $shareCostValue);
    }

    private function createCostDto(?array $cost, ?float $shareCostValue): CostDto
    {
        $shareCost = $cost['share_cost'] ?? null;
        $accetsCost = $cost['accets_cost'] ?? null;
        $costDate = $cost['date'] ? new DateTimeImmutable($cost['date']->format('Y-m-d')) : null;
        $percent = null;

        if (null !== $shareCostValue && null !== $shareCost) {
            $percent = ($shareCostValue - $shareCost ) / $shareCost* 100;
        }
        return new CostDto(
            (float) $shareCost,
            (float) $accetsCost,
            $costDate,
            $percent
        );
    }
}