<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

use Carbon\Carbon;
use Cron\CronExpression;
use DateTimeInterface;
use DateTimeZone;

trait ManagesCronExpression
{
    private string $cronExpression = '* * * * *';
    /** @var array<callable> */
    private array $filters = [];
    /** @var array<callable> */
    private array $rejects = [];
    private DateTimeZone $timeZone;

    public function cronExpression(): string
    {
        return $this->cronExpression;
    }

    public function withCronExpression(string $expression): static
    {
        $instance = clone $this;
        $instance->cronExpression = $expression;

        return $instance;
    }

    public function withTimeZone(DateTimeZone $timeZone): static
    {
        $instance = clone $this;
        $instance->timeZone = $timeZone;

        return $instance;
    }

    public function when(callable|bool $callback): static
    {
        $instance = clone $this;
        $instance->filters[] = is_callable($callback) ? $callback : fn () => $callback;

        return $instance;
    }

    public function skip(callable|bool $callback): static
    {
        $instance = clone $this;
        $instance->rejects[] = is_callable($callback) ? $callback : fn () => $callback;

        return $instance;
    }

    /**
     * Schedule between start and end time.
     */
    public function between(string $startTime, string $endTime): static
    {
        return $this->when($this->inTimeInterval($startTime, $endTime));
    }

    /**
     * Schedule not between start and end time.
     */
    public function unlessBetween(string $startTime, string $endTime): static
    {
        return $this->skip($this->inTimeInterval($startTime, $endTime));
    }

    /**
     * Schedule the event to run between start and end time.
     */
    private function inTimeInterval(string $startTime, string $endTime): callable
    {
        [$now, $startTime, $endTime] = [
            Carbon::now($this->timeZone ?? null),
            Carbon::parse($startTime, $this->timeZone ?? null),
            Carbon::parse($endTime, $this->timeZone ?? null),
        ];

        if ($endTime->lessThan($startTime)) {
            if ($startTime->greaterThan($now)) {
                $startTime->subDay();
            } else {
                $endTime->addDay();
            }
        }

        return function () use ($now, $startTime, $endTime) {
            return $now->between($startTime, $endTime);
        };
    }

    /**
     * Schedule the event to run every minute.
     */
    public function everyMinute(): static
    {
        return $this->spliceIntoPosition(1, '*');
    }

    /**
     * Schedule the event to run every two minutes.
     */
    public function everyTwoMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/2');
    }

    /**
     * Schedule the event to run every three minutes.
     */
    public function everyThreeMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/3');
    }

    /**
     * Schedule the event to run every four minutes.
     */
    public function everyFourMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/4');
    }

    /**
     * Schedule the event to run every five minutes.
     */
    public function everyFiveMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/5');
    }

    /**
     * Schedule the event to run every ten minutes.
     */
    public function everyTenMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/10');
    }

    /**
     * Schedule the event to run every fifteen minutes.
     */
    public function everyFifteenMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/15');
    }

    /**
     * Schedule the event to run every thirty minutes.
     */
    public function everyThirtyMinutes(): static
    {
        return $this->spliceIntoPosition(1, '0,30');
    }

    /**
     * Schedule the event to run hourly.
     */
    public function hourly(): static
    {
        return $this->spliceIntoPosition(1, 0);
    }

    /**
     * Schedule the event to run hourly at a given offset in the hour.
     */
    /** @param array<int>|int $offset */
    public function hourlyAt(array|int $offset): static
    {
        $offset = is_array($offset) ? implode(',', $offset) : $offset;

        return $this->spliceIntoPosition(1, $offset);
    }

    /**
     * Schedule the event to run every two hours.
     */
    public function everyTwoHours(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, '*/2');
    }

    /**
     * Schedule the event to run every three hours.
     */
    public function everyThreeHours(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, '*/3');
    }

    /**
     * Schedule the event to run every four hours.
     */
    public function everyFourHours(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, '*/4');
    }

    /**
     * Schedule the event to run every six hours.
     */
    public function everySixHours(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, '*/6');
    }

    /**
     * Schedule the event to run daily.
     */
    public function daily(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0);
    }

    /**
     * Schedule the command at a given time.
     */
    public function at(string $time): static
    {
        return $this->dailyAt($time);
    }

    /**
     * Schedule the event to run daily at a given time (10:00, 19:30, etc).
     */
    public function dailyAt(string $time): static
    {
        $segments = explode(':', $time);

        return $this->spliceIntoPosition(2, (int) $segments[0])
            ->spliceIntoPosition(1, count($segments) === 2 ? (int) $segments[1] : '0');
    }

    /**
     * Schedule the event to run twice daily.
     */
    public function twiceDaily(int $first = 1, int $second = 13): static
    {
        return $this->twiceDailyAt($first, $second, 0);
    }

    /**
     * Schedule the event to run twice daily at a given offset.
     */
    public function twiceDailyAt(int $first = 1, int $second = 13, int $offset = 0): static
    {
        $hours = $first.','.$second;

        return $this->spliceIntoPosition(1, $offset)
            ->spliceIntoPosition(2, $hours);
    }

    /**
     * Schedule the event to run only on weekdays.
     */
    public function weekdays(): static
    {
        return $this->days(Cron::MONDAY.'-'.Cron::FRIDAY);
    }

    /**
     * Schedule the event to run only on weekends.
     */
    public function weekends(): static
    {
        return $this->days(Cron::SATURDAY.','.Cron::SUNDAY);
    }

    /**
     * Schedule the event to run only on Mondays.
     */
    public function mondays(): static
    {
        return $this->days(Cron::MONDAY);
    }

    /**
     * Schedule the event to run only on Tuesdays.
     */
    public function tuesdays(): static
    {
        return $this->days(Cron::TUESDAY);
    }

    /**
     * Schedule the event to run only on Wednesdays.
     */
    public function wednesdays(): static
    {
        return $this->days(Cron::WEDNESDAY);
    }

    /**
     * Schedule the event to run only on Thursdays.
     */
    public function thursdays(): static
    {
        return $this->days(Cron::THURSDAY);
    }

    /**
     * Schedule the event to run only on Fridays.
     */
    public function fridays(): static
    {
        return $this->days(Cron::FRIDAY);
    }

    /**
     * Schedule the event to run only on Saturdays.
     */
    public function saturdays(): static
    {
        return $this->days(Cron::SATURDAY);
    }

    /**
     * Schedule the event to run only on Sundays.
     */
    public function sundays(): static
    {
        return $this->days(Cron::SUNDAY);
    }

    /**
     * Schedule the event to run weekly.
     */
    public function weekly(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(5, 0);
    }

    /**
     * Schedule the event to run weekly on a given day and time.
     */
    /** @param array<int>|int $dayOfWeek */
    public function weeklyOn(array|int $dayOfWeek, string $time = '0:0'): static
    {
        $this->dailyAt($time);

        return $this->days($dayOfWeek);
    }

    /**
     * Schedule the event to run monthly.
     */
    public function monthly(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1);
    }

    /**
     * Schedule the event to run monthly on a given day and time.
     */
    public function monthlyOn(int $dayOfMonth = 1, string $time = '0:0'): static
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, $dayOfMonth);
    }

    /**
     * Schedule the event to run twice monthly at a given time.
     */
    public function twiceMonthly(int $first = 1, int $second = 16, string $time = '0:0'): static
    {
        $daysOfMonth = $first.','.$second;

        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, $daysOfMonth);
    }

    /**
     * Schedule the event to run on the last day of the month.
     */
    public function lastDayOfMonth(string $time = '0:0'): static
    {
        return $this->dailyAt($time)
            ->spliceIntoPosition(3, Carbon::now()->endOfMonth()->day);
    }

    /**
     * Schedule the event to run quarterly.
     */
    public function quarterly(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, '1-12/3');
    }

    /**
     * Schedule the event to run yearly.
     */
    public function yearly(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, 1);
    }

    /**
     * Schedule the event to run yearly on a given month, day, and time.
     */
    public function yearlyOn(int $month = 1, int|string $dayOfMonth = 1, string $time = '0:0'): static
    {
        return $this->dailyAt($time)
            ->spliceIntoPosition(3, $dayOfMonth)
            ->spliceIntoPosition(4, $month);
    }

    /**
     * Set the days of the week the command should run on.
     */
    /** @param array<int|string>|int|string $days */
    public function days(array|int|string $days): static
    {
        $days = is_array($days) ? $days : func_get_args();

        return $this->spliceIntoPosition(5, implode(',', $days));
    }

    /**
     * Splice the given value into the given position of the expression.
     */
    protected function spliceIntoPosition(int $position, string|int $value): static
    {
        $segments = explode(' ', $this->cronExpression);

        $segments[$position - 1] = $value;

        return $this->withCronExpression(implode(' ', $segments));
    }

    public function isDue(Carbon|DateTimeInterface|string|null $when = null): bool
    {
        $when = $this->toCarbon($when);

        return $this->passesCronExpression($when);
    }

    public function currentlyDueAt(Carbon|DateTimeInterface|string|null $when = null): Carbon
    {
        $when = $this->toCarbon($when);

        return $this->toCarbon((new CronExpression($this->cronExpression))->getNextRunDate($when, 0, true));
    }

    protected function toCarbon(Carbon|DateTimeInterface|string|null $when = null): Carbon
    {
        return Utils::toCarbon($when, $this->timeZone ?? null);
    }

    protected function passesCronExpression(Carbon|DateTimeInterface|string|null $when = null): bool
    {
        return (new CronExpression($this->cronExpression))
            ->isDue(Utils::toCarbon($when)->toDateTimeString());
    }
}
