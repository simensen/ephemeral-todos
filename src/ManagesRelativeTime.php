<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

trait ManagesRelativeTime
{
    private int $timeInSeconds = 0;

    public function toTime(): Time
    {
        return new Time($this->timeInSeconds);
    }

    public function timeInSeconds(): int
    {
        return $this->timeInSeconds;
    }

    public static function oneMinute(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 60;

        return $instance;
    }

    public static function twoMinutes(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 120;

        return $instance;
    }

    public static function fiveMinutes(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 300;

        return $instance;
    }

    public static function tenMinutes(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 600;

        return $instance;
    }

    public static function fifteenMinutes(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 900;

        return $instance;
    }

    public static function twentyMinutes(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 1200;

        return $instance;
    }

    public static function thirtyMinutes(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 1800;

        return $instance;
    }

    public static function fortyFiveMinutes(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 2700;

        return $instance;
    }

    public static function sixtyMinutes(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 3600;

        return $instance;
    }

    public static function ninetyMinutes(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 5400;

        return $instance;
    }

    public static function oneHour(): self
    {
        return self::sixtyMinutes();
    }

    public static function twoHours(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 7200;

        return $instance;
    }

    public static function threeHours(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 10800;

        return $instance;
    }

    public static function fourHours(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 14400;

        return $instance;
    }

    public static function sixHours(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 21600;

        return $instance;
    }

    public static function twelveHours(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 43200;

        return $instance;
    }

    public static function oneDay(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 86400;

        return $instance;
    }

    public static function twoDays(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 172800;

        return $instance;
    }

    public static function threeDays(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 259200;

        return $instance;
    }

    public static function fourDays(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 345600;

        return $instance;
    }

    public static function fiveDays(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 432000;

        return $instance;
    }

    public static function sixDays(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 518400;

        return $instance;
    }

    public static function sevenDays(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 604800;

        return $instance;
    }

    public static function oneWeek(): self
    {
        return self::sevenDays();
    }

    public static function twoWeeks(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 1209600;

        return $instance;
    }

    public static function threeWeeks(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 1814400;

        return $instance;
    }
}
