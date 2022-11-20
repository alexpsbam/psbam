<?php
declare(strict_types=1);

namespace fund\Enum\Db;

class GroupCostEnum
{
    public const WITHOUT_GROUP_BY = 1;
    public const GROUP_BY_WEEK = 2;
    public const GROUP_BY_MONTH = 3;
}
