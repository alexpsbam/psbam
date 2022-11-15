<?php
declare(strict_types=1);

namespace fund\Enum;

/** @todo relocate to DB */
class LastWorkDaysEnum
{
    public const LAST_WORK_DAYS = [
        2022 => [
            MonthEnum::DEC => '30-12-2022',
            MonthEnum::NOV => '30-11-2022',
            MonthEnum::OCT => '31-10-2022',
            MonthEnum::SEP => '30-09-2022',
            MonthEnum::AUG => '31-08-2022',
            MonthEnum::JUL => '29-07-2022',
            MonthEnum::JUN => '30-06-2022',
        ],
        2023 => [
            MonthEnum::JUN => '31-01-2023',
            MonthEnum::FEB => '28-02-2023',
        ],
    ];
}