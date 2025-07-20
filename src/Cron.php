<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

class Cron
{
    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;

    const SECONDS = 0;
    const MINUTES = 1;
    const HOURS = 2;
    const DAY_OF_MONTH = 3;
    const MONTH = 4;
    const DAY_OF_WEEK = 5;
    const YEAR = 6;
}
