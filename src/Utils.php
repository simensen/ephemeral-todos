<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use DateTimeImmutable;
use DateTimeZone;

final class Utils
{
    public static function equalToTheMinute(DateTimeInterface|DateTimeImmutable $first, DateTimeInterface|DateTimeImmutable $second): bool
    {
        return self::toCarbon($first)->clone()->microseconds(0)->seconds(0)->equalTo(self::toCarbon($second)->clone()->microseconds(0)->seconds(0));
    }

    public static function toCarbon(DateTimeInterface|DateTimeImmutable|string $when, ?DateTimeZone $timeZone = null): CarbonImmutable
    {
        if ($when instanceof CarbonImmutable) {
            return $when;
        }

        if ($when instanceof CarbonInterface) {
            return $when->toImmutable();
        }

        return new CarbonImmutable($when, $timeZone);
    }
}
