<?php
declare(strict_types=1);

namespace fund\Helper;

use DateTimeImmutable;
use Exception;
use fund\Enum\CostPeriodIdEnum;
use fund\Enum\LastWorkDaysEnum;

class DateHelper
{
    public function getDatesForCost(DateTimeImmutable $dateStart): array
    {
        $dates[CostPeriodIdEnum::LAST_DAY] = new DateTimeImmutable();
        $dates[CostPeriodIdEnum::LAST_WORK_PERIOD] = $dateStart;
        foreach (CostPeriodIdEnum::PERIODS as $key => $interval) {
            $dates[$key] = $dateStart->modify($interval);
        }
        return $dates;
    }

    public function getLastWorkDataByDate(DateTimeImmutable $date): DateTimeImmutable
    {
        $monthLastWork = (int) $date->format('m');
        $yearLastWork = (int) $date->format('Y');
        $dateString = LastWorkDaysEnum::LAST_WORK_DAYS[$yearLastWork][$monthLastWork];
        return new DateTimeImmutable($dateString);
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateFromByPeriodId(int $periodId, DateTimeImmutable $dateFrom): DateTimeImmutable
    {
        if (true === key_exists($periodId, CostPeriodIdEnum::PERIODS)) {
            return $dateFrom->modify(CostPeriodIdEnum::PERIODS[$periodId]);
        }

        throw new Exception('periodId is not found: ' . $periodId);
    }
}