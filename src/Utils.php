<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

use Carbon\Carbon;
use DateTimeInterface;
use DateTimeZone;

final class Utils
{
    public static function equalToTheMinute(Carbon|DateTimeInterface|string|null $first, Carbon|DateTimeInterface|string|null $second): bool
    {
        return self::toCarbon($first)->clone()->microseconds(0)->seconds(0)->equalTo(self::toCarbon($second)->clone()->microseconds(0)->seconds(0));
    }

    public static function toCarbon(Carbon|DateTimeInterface|string|null $when = null, ?DateTimeZone $timeZone = null): Carbon
    {
        return ($when instanceof Carbon) ? $when : new Carbon($when, $timeZone ?? null);
    }
}
