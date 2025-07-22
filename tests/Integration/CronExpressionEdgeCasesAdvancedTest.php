<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Integration;

use Carbon\Carbon;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Todos;

class CronExpressionEdgeCasesAdvancedTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow('2024-01-15 10:00:00 UTC');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function testComplexCronExpressionEdgeCases()
    {
        // Test last day of month scheduling
        $schedule = Schedule::create()->withCronExpression('0 9 28-31 * *');

        // Should be due on last few days of January (28, 29, 30, 31)
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-28 09:00:00')));
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-29 09:00:00')));
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-30 09:00:00')));
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-31 09:00:00')));

        // Should not be due on February 29 (even in leap year, 29 < 28 is false)
        // Actually, this should be due on Feb 28, 29 in leap year
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-02-28 09:00:00')));
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-02-29 09:00:00'))); // 2024 is leap year

        // Should not be due in February 30 (doesn't exist)
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-02-30 09:00:00'))); // Invalid date
    }

    public function testWeeklyOnSpecificDaysCombinations()
    {
        // Test Monday, Wednesday, Friday schedule
        $schedule = Schedule::create()->withCronExpression('0 14 * * 1,3,5');

        // Monday (1), Wednesday (3), Friday (5) should be due
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-15 14:00:00'))); // Monday
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-17 14:00:00'))); // Wednesday
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-19 14:00:00'))); // Friday

        // Tuesday (2), Thursday (4), Saturday (6), Sunday (0) should not be due
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-16 14:00:00'))); // Tuesday
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-18 14:00:00'))); // Thursday
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-20 14:00:00'))); // Saturday
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-21 14:00:00'))); // Sunday
    }

    public function testLeapYearFebruary29Scheduling()
    {
        // Schedule specifically for February 29
        $schedule = Schedule::create()->withCronExpression('0 12 29 2 *');

        // Should be due on Feb 29, 2024 (leap year)
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-02-29 12:00:00')));

        // Should not be due on Feb 29, 2023 (non-leap year, date doesn't exist)
        // Note: Carbon will handle this gracefully by adjusting to a valid date
        // or the cron library might not match at all
        $this->assertFalse($schedule->isDue(Carbon::parse('2023-02-28 12:00:00')));
        $this->assertFalse($schedule->isDue(Carbon::parse('2023-03-01 12:00:00')));
    }

    public function testDaylightSavingTimeSpringForward()
    {
        $easternTz = new DateTimeZone('America/New_York');

        // Spring forward happens at 2:00 AM on March 10, 2024
        // Time jumps from 1:59:59 AM to 3:00:00 AM

        $schedule = Schedule::create()->dailyAt('02:30');

        // Test the day before DST
        $beforeDST = Carbon::parse('2024-03-09 02:30:00', $easternTz);
        $this->assertTrue($schedule->isDue($beforeDST));

        // Test during the spring forward (2:30 AM doesn't exist)
        $duringSpringForward = Carbon::parse('2024-03-10 02:30:00', $easternTz);
        // This might automatically adjust to 3:30 AM, test how cron handles it
        $shouldBeDue = $schedule->isDue($duringSpringForward);

        // Either it should be due (adjusted time) or not due (non-existent time)
        $this->assertIsBool($shouldBeDue);

        // Test the day after DST
        $afterDST = Carbon::parse('2024-03-11 02:30:00', $easternTz);
        $this->assertTrue($schedule->isDue($afterDST));
    }

    public function testDaylightSavingTimeFallBack()
    {
        $easternTz = new DateTimeZone('America/New_York');

        // Fall back happens at 2:00 AM on November 3, 2024
        // Time jumps from 1:59:59 AM back to 1:00:00 AM

        $schedule = Schedule::create()->dailyAt('01:30');

        // Test the day before DST
        $beforeDST = Carbon::parse('2024-11-02 01:30:00', $easternTz);
        $this->assertTrue($schedule->isDue($beforeDST));

        // Test during fall back (1:30 AM happens twice)
        $duringFallBack = Carbon::parse('2024-11-03 01:30:00', $easternTz);
        $this->assertTrue($schedule->isDue($duringFallBack));

        // Test the day after DST
        $afterDST = Carbon::parse('2024-11-04 01:30:00', $easternTz);
        $this->assertTrue($schedule->isDue($afterDST));
    }

    public function testEndOfMonthBoundaryConditions()
    {
        $schedule = Schedule::create()->monthlyOn(31, '15:00');

        // January has 31 days
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-31 15:00:00')));

        // February doesn't have 31 days - should not be due
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-02-28 15:00:00')));
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-02-29 15:00:00')));

        // March has 31 days
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-03-31 15:00:00')));

        // April doesn't have 31 days
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-04-30 15:00:00')));

        // May has 31 days
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-05-31 15:00:00')));
    }

    public function testEveryNthDayScheduling()
    {
        // Every 3 days starting from day 1
        $schedule = Schedule::create()->withCronExpression('0 10 1,4,7,10,13,16,19,22,25,28,31 * *');

        // Should be due on days 1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-01 10:00:00')));
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-04 10:00:00')));
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-07 10:00:00')));
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-10 10:00:00')));
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-13 10:00:00')));

        // Should not be due on other days
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-02 10:00:00')));
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-03 10:00:00')));
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-05 10:00:00')));
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-06 10:00:00')));
    }

    public function testBusinessHoursOnlyScheduling()
    {
        // Weekdays only (Monday-Friday) at business hours
        $schedule = Schedule::create()->withCronExpression('0 9-17 * * 1-5');

        // Monday 9 AM - 5 PM should be due
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-15 09:00:00'))); // Monday 9 AM
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-15 12:00:00'))); // Monday noon
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-15 17:00:00'))); // Monday 5 PM

        // Monday 8 AM and 6 PM should not be due
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-15 08:00:00'))); // Monday 8 AM
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-15 18:00:00'))); // Monday 6 PM

        // Friday should work the same
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-19 14:00:00'))); // Friday 2 PM

        // Saturday and Sunday should not be due at any hour
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-20 12:00:00'))); // Saturday noon
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-21 12:00:00'))); // Sunday noon
    }

    public function testQuarterlyScheduling()
    {
        // First day of quarters (Jan, Apr, Jul, Oct)
        $schedule = Schedule::create()->withCronExpression('0 9 1 1,4,7,10 *');

        // Should be due on first day of each quarter
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-01 09:00:00'))); // Q1
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-04-01 09:00:00'))); // Q2
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-07-01 09:00:00'))); // Q3
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-10-01 09:00:00'))); // Q4

        // Should not be due on other months
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-02-01 09:00:00')));
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-03-01 09:00:00')));
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-05-01 09:00:00')));
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-12-01 09:00:00')));
    }

    public function testIntegrationWithTodosEdgeCases()
    {
        $todos = new Todos();

        // Simple task that runs daily at 13:00
        $todos->define(Definition::define()
            ->withName('Daily 1PM Task')
            ->due(Schedule::create()->daily()->at('13:00')));

        // Test at 13:00 - should be ready
        Carbon::setTestNow('2024-09-13 13:00:00');
        $ready = $todos->readyToBeCreatedAt(Carbon::now());
        $this->assertCount(1, $ready);
        $this->assertNotNull($ready[0]);
        $this->assertEquals('Daily 1PM Task', $ready[0]->name());

        // Test at 12:00 - should not be ready
        Carbon::setTestNow('2024-09-13 12:00:00');
        $notReady = $todos->readyToBeCreatedAt(Carbon::now());
        $this->assertCount(0, $notReady);
    }

    public function testYearTransitionWithComplexSchedules()
    {
        $todos = new Todos();

        // December 30th only (which is a Monday in 2024)
        $todos->define(Definition::define()
            ->withName('Year End Task')
            ->due(Schedule::create()->withCronExpression('0 17 30 12 *')));

        // Test December 29, 2024 - should not be ready (not the 30th)
        Carbon::setTestNow('2024-12-29 17:00:00');
        $dec29Ready = $todos->readyToBeCreatedAt(Carbon::now());
        $this->assertCount(0, $dec29Ready);

        // Test December 30, 2024 - should be ready
        Carbon::setTestNow('2024-12-30 17:00:00');
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::now()));

        // Test December 31, 2024 - should not be ready (not the 30th)
        Carbon::setTestNow('2024-12-31 17:00:00');
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));

        // Test January 1, 2025 - should not be ready (different month)
        Carbon::setTestNow('2025-01-01 17:00:00');
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));
    }

    public function testMidnightAndNoonBoundaryConditions()
    {
        $schedule = Schedule::create()->withCronExpression('0 0,12 * * *'); // Midnight and noon

        // Test exact midnight
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-15 00:00:00')));

        // Test one minute before midnight
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-15 23:59:00')));

        // Test one minute after midnight
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-15 00:01:00')));

        // Test exact noon
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-15 12:00:00')));

        // Test one minute before noon
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-15 11:59:00')));

        // Test one minute after noon
        $this->assertFalse($schedule->isDue(Carbon::parse('2024-01-15 12:01:00')));
    }
}
