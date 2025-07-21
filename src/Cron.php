<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

class Cron
{
    public const SUNDAY = 0;
    public const MONDAY = 1;
    public const TUESDAY = 2;
    public const WEDNESDAY = 3;
    public const THURSDAY = 4;
    public const FRIDAY = 5;
    public const SATURDAY = 6;

    public const SECONDS = 0;
    public const MINUTES = 1;
    public const HOURS = 2;
    public const DAY_OF_MONTH = 3;
    public const MONTH = 4;
    public const DAY_OF_WEEK = 5;
    public const YEAR = 6;
}
