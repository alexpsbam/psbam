<?php
declare(strict_types=1);

namespace fund\Enum;

class CostPeriodIdEnum
{
    public const LAST_DAY = 1;

    public const LAST_1_MOUTH = 2;
    public const LAST_3_MOUTH = 3;
    public const LAST_6_MOUTH = 4;

    public const LAST_1_YEAR = 5;
    public const LAST_3_YEAR = 6;
    public const LAST_5_YEAR = 7;

    public const LAST_WORK_PERIOD = 10;

    public const PERIODS = [
        self::LAST_1_MOUTH => '-1 MONTH',
        self::LAST_3_MOUTH  => '-3 MONTH',
        self::LAST_6_MOUTH  => '-6 MONTH',
        self::LAST_1_YEAR => '-1 YEAR',
        self::LAST_3_YEAR => '-3 YEAR',
        self::LAST_5_YEAR => '-5 YEAR',
    ];

    public const ALL_KEYS = [
        self::LAST_DAY,
        self::LAST_WORK_PERIOD,
        self::LAST_1_MOUTH,
        self::LAST_3_MOUTH,
        self::LAST_6_MOUTH,
        self::LAST_1_YEAR,
        self::LAST_3_YEAR,
        self::LAST_5_YEAR,
    ];
}