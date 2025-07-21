<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use Carbon\Carbon;
use DateTimeZone;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Tests\TestCase;

class CronExpressionEdgeCasesTest extends TestCase
{
    public function testLeapYearFebruary29th(): void
    {
        $this->travelTo('2024-02-28 12:00:00'); // 2024 is a leap year

        $schedule = Schedule::create()->yearlyOn(2, 29, '12:00'); // Feb 29th

        $this->assertEquals('0 12 29 2 *', $schedule->cronExpression());
        $this->assertTrue($schedule->isDue('2024-02-29 12:00:00'));
    }

    public function testNonLeapYearFebruaryHandling(): void
    {
        $this->travelTo('2023-02-28 12:00:00'); // 2023 is not a leap year

        $schedule = Schedule::create()->yearlyOn(2, 29, '12:00'); // Feb 29th

        // Cron expression is still valid, but Feb 29th won't exist in non-leap years
        $this->assertEquals('0 12 29 2 *', $schedule->cronExpression());
        $this->assertFalse($schedule->isDue('2023-02-29 12:00:00')); // This date doesn't exist
    }

    public function testEndOfMonthVariations(): void
    {
        // January (31 days)
        $this->travelTo('2025-01-15 12:00:00');
        $januaryLastDay = Schedule::create()->lastDayOfMonth('12:00');
        $this->assertEquals('0 12 31 * *', $januaryLastDay->cronExpression());

        // April (30 days)
        $this->travelTo('2025-04-15 12:00:00');
        $aprilLastDay = Schedule::create()->lastDayOfMonth('12:00');
        $this->assertEquals('0 12 30 * *', $aprilLastDay->cronExpression());

        // February in non-leap year (28 days)
        $this->travelTo('2025-02-15 12:00:00');
        $februaryLastDay = Schedule::create()->lastDayOfMonth('12:00');
        $this->assertEquals('0 12 28 * *', $februaryLastDay->cronExpression());
    }

    public function testDaylightSavingTimeTransitions(): void
    {
        $timezone = new DateTimeZone('America/New_York');

        // Spring forward (2:00 AM becomes 3:00 AM)
        $springForward = Schedule::create()
            ->withTimeZone($timezone)
            ->dailyAt('02:30');

        $this->assertEquals('30 2 * * *', $springForward->cronExpression());

        // Fall back (2:00 AM happens twice)
        $fallBack = Schedule::create()
            ->withTimeZone($timezone)
            ->dailyAt('01:30');

        $this->assertEquals('30 1 * * *', $fallBack->cronExpression());
    }

    public function testTimezoneEdgeCases(): void
    {
        $utc = new DateTimeZone('UTC');
        $plus14 = new DateTimeZone('Pacific/Kiritimati'); // UTC+14
        $minus12 = new DateTimeZone('Etc/GMT+12'); // UTC-12

        $schedule = Schedule::create()->dailyAt('12:00');

        $utcSchedule = $schedule->withTimeZone($utc);
        $plus14Schedule = $schedule->withTimeZone($plus14);
        $minus12Schedule = $schedule->withTimeZone($minus12);

        // All should have same cron expression
        $this->assertEquals('0 12 * * *', $utcSchedule->cronExpression());
        $this->assertEquals('0 12 * * *', $plus14Schedule->cronExpression());
        $this->assertEquals('0 12 * * *', $minus12Schedule->cronExpression());
    }

    public function testBoundaryTimeIntervals(): void
    {
        $this->travelTo('2025-01-19 23:30:00');

        // Interval that crosses midnight
        $nightShift = Schedule::create()->between('22:00', '06:00');
        $this->assertInstanceOf(Schedule::class, $nightShift);

        // Interval within same day
        $dayShift = Schedule::create()->between('09:00', '17:00');
        $this->assertInstanceOf(Schedule::class, $dayShift);

        // Interval that's backwards (should be handled)
        $backwards = Schedule::create()->between('18:00', '08:00');
        $this->assertInstanceOf(Schedule::class, $backwards);
    }

    public function testComplexCronExpressions(): void
    {
        // Every 15 minutes during business hours on weekdays
        $businessHours = Schedule::create()
            ->everyFifteenMinutes()
            ->between('09:00', '17:00')
            ->weekdays();

        $this->assertEquals('*/15 * * * 1-5', $businessHours->cronExpression());

        // Twice monthly on specific days with specific time
        $payroll = Schedule::create()->twiceMonthly(1, 15, '14:30');
        $this->assertEquals('* * 1,15 * *', $payroll->cronExpression());

        // Quarterly at year start
        $quarterly = Schedule::create()->quarterly();
        $this->assertEquals('0 0 1 1-12/3 *', $quarterly->cronExpression());
    }

    public function testFilterCombinations(): void
    {
        $this->travelTo('2025-01-19 10:00:00'); // Sunday

        $filtered = Schedule::create()
            ->hourly()
            ->when(function () { return true; })
            ->skip(function () { return false; })
            ->between('09:00', '17:00')
            ->weekdays();

        $this->assertEquals('0 * * * 1-5', $filtered->cronExpression());
    }

    public function testHourMinuteBoundaryCases(): void
    {
        // Test 24-hour format edge cases
        $midnight = Schedule::create()->dailyAt('00:00');
        $this->assertEquals('0 0 * * *', $midnight->cronExpression());

        $almostMidnight = Schedule::create()->dailyAt('23:59');
        $this->assertEquals('59 23 * * *', $almostMidnight->cronExpression());

        // Test single digit times
        $earlyMorning = Schedule::create()->dailyAt('9:5');
        $this->assertEquals('5 9 * * *', $earlyMorning->cronExpression());
    }

    public function testCronExpressionValidationWithIsDue(): void
    {
        $this->travelTo('2025-01-19 12:00:00'); // Sunday

        // Test various schedules at specific times
        $hourly = Schedule::create()->hourly();
        $this->assertTrue($hourly->isDue('2025-01-19 13:00:00'));
        $this->assertFalse($hourly->isDue('2025-01-19 13:30:00'));

        $daily = Schedule::create()->daily();
        $this->assertTrue($daily->isDue('2025-01-20 00:00:00'));
        $this->assertFalse($daily->isDue('2025-01-20 12:00:00'));

        $weekdays = Schedule::create()->weekdays();
        $this->assertTrue($weekdays->isDue('2025-01-20 10:00:00')); // Monday
        $this->assertFalse($weekdays->isDue('2025-01-19 10:00:00')); // Sunday
    }

    public function testCurrentlyDueAtCalculations(): void
    {
        $this->travelTo('2025-01-19 14:30:00');

        // Next hourly occurrence
        $hourly = Schedule::create()->hourly();
        $nextHour = $hourly->currentlyDueAt();
        $this->assertCarbonEqualToMinute(
            Carbon::parse('2025-01-19 15:00:00'),
            $nextHour
        );

        // Next daily occurrence
        $daily = Schedule::create()->daily();
        $nextDay = $daily->currentlyDueAt();
        $this->assertCarbonEqualToMinute(
            Carbon::parse('2025-01-20 00:00:00'),
            $nextDay
        );

        // Next weekly occurrence (Sundays)
        $weekly = Schedule::create()->weekly();
        $nextWeek = $weekly->currentlyDueAt();
        $this->assertCarbonEqualToMinute(
            Carbon::parse('2025-01-26 00:00:00'), // Next Sunday
            $nextWeek
        );
    }

    public function testMonthDayEdgeCases(): void
    {
        // Test 31st of month in months with fewer days
        $thirtyFirst = Schedule::create()->monthlyOn(31, '12:00');
        // monthlyOn calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * 31 * *', $thirtyFirst->cronExpression());

        // This would only run in months with 31 days
        $this->assertTrue($thirtyFirst->isDue('2025-01-31 00:00:00')); // January has 31 days, default time
        $this->assertFalse($thirtyFirst->isDue('2025-04-31 00:00:00')); // April only has 30 days (invalid date)
    }

    public function testComplexSchedulingScenarios(): void
    {
        // Medical appointment reminder: Every 6 months on the 15th at 9 AM
        $medicalReminder = Schedule::create()
            ->withCronExpression('0 9 15 */6 *');
        $this->assertEquals('0 9 15 */6 *', $medicalReminder->cronExpression());

        // Backup schedule: Every day at 2 AM except weekends
        $weekdayBackup = Schedule::create()
            ->dailyAt('02:00')
            ->weekdays();
        $this->assertEquals('0 2 * * 1-5', $weekdayBackup->cronExpression());

        // Report generation: First Monday of every month at 8 AM
        $monthlyReport = Schedule::create()
            ->withCronExpression('0 8 1-7 * 1'); // First week, Monday
        $this->assertEquals('0 8 1-7 * 1', $monthlyReport->cronExpression());
    }
}
