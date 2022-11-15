<?php
declare(strict_types=1);

namespace fund\Enum;

class CostColumnEnum
{
    public const NAME_FUND = 'name';
    public const LIST = [
        self::NAME_FUND => 'Выбранные пифы',
        CostPeriodIdEnum::LAST_DAY => 'За',
        CostPeriodIdEnum::LAST_WORK_PERIOD => 'За',
        CostPeriodIdEnum::LAST_1_MOUTH => 'За 1 меся',
        CostPeriodIdEnum::LAST_3_MOUTH => 'За 3 месяца',
        CostPeriodIdEnum::LAST_6_MOUTH => 'За 6 месяцев',
        CostPeriodIdEnum::LAST_1_YEAR => 'За год',
        CostPeriodIdEnum::LAST_3_YEAR => 'За 3 года',
        CostPeriodIdEnum::LAST_5_YEAR => 'За 5 лет',
    ];
}