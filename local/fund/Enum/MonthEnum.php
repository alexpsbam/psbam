<?php
declare(strict_types=1);

namespace fund\Enum;

class MonthEnum
{
    public const DEC = 12;
    public const NOV = 11;
    public const OCT = 10;
    public const SEP = 9;
    public const AUG = 8;
    public const JUL = 7;
    public const JUN = 6;
    public const MAY = 5;
    public const APR = 4;
    public const MAR = 3;
    public const FEB = 2;
    public const JAN = 1;

    public const ALL = [
      self::DEC,
      self::NOV,
      self::OCT,
      self::SEP,
      self::AUG,
      self::JUL,
      self::JUN,
      self::MAY,
      self::APR,
      self::MAR,
      self::FEB,
      self::JAN,
    ];
}
