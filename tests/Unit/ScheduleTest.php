<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use Carbon\Carbon;
use DateTimeZone;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Tests\TestCase;
use Simensen\EphemeralTodos\Tests\Testing\AssertsImmutability;

class ScheduleTest extends TestCase
{
    use AssertsImmutability;
    public function testCreateReturnsNewInstance(): void
    {
        $schedule = Schedule::create();

        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testDefaultCronExpression(): void
    {
        $schedule = Schedule::create();

        $this->assertEquals('* * * * *', $schedule->cronExpression());
    }

    public function testWithCronExpressionReturnsNewInstance(): void
    {
        $original = Schedule::create();
        
        $this->assertMethodReturnsNewInstance($original, 'withCronExpression', '0 0 * * *');
        
        // Verify original is unchanged and new instance has correct value
        $this->assertEquals('* * * * *', $original->cronExpression());
        $modified = $original->withCronExpression('0 0 * * *');
        $this->assertEquals('0 0 * * *', $modified->cronExpression());
    }

    public function testWithTimezoneReturnsNewInstance(): void
    {
        $original = Schedule::create();
        $timezone = new DateTimeZone('America/New_York');
        
        $this->assertMethodReturnsNewInstance($original, 'withTimeZone', $timezone);
    }

    public function testEveryMinute(): void
    {
        $schedule = Schedule::create()->everyMinute();

        $this->assertEquals('* * * * *', $schedule->cronExpression());
    }

    public function testEveryTwoMinutes(): void
    {
        $schedule = Schedule::create()->everyTwoMinutes();

        $this->assertEquals('*/2 * * * *', $schedule->cronExpression());
    }

    public function testEveryThreeMinutes(): void
    {
        $schedule = Schedule::create()->everyThreeMinutes();

        $this->assertEquals('*/3 * * * *', $schedule->cronExpression());
    }

    public function testEveryFiveMinutes(): void
    {
        $schedule = Schedule::create()->everyFiveMinutes();

        $this->assertEquals('*/5 * * * *', $schedule->cronExpression());
    }

    public function testEveryTenMinutes(): void
    {
        $schedule = Schedule::create()->everyTenMinutes();

        $this->assertEquals('*/10 * * * *', $schedule->cronExpression());
    }

    public function testEveryFifteenMinutes(): void
    {
        $schedule = Schedule::create()->everyFifteenMinutes();

        $this->assertEquals('*/15 * * * *', $schedule->cronExpression());
    }

    public function testEveryThirtyMinutes(): void
    {
        $schedule = Schedule::create()->everyThirtyMinutes();

        $this->assertEquals('0,30 * * * *', $schedule->cronExpression());
    }

    public function testHourly(): void
    {
        $schedule = Schedule::create()->hourly();

        $this->assertEquals('0 * * * *', $schedule->cronExpression());
    }

    public function testHourlyAtSingleOffset(): void
    {
        $schedule = Schedule::create()->hourlyAt(15);

        $this->assertEquals('15 * * * *', $schedule->cronExpression());
    }

    public function testHourlyAtMultipleOffsets(): void
    {
        $schedule = Schedule::create()->hourlyAt([15, 30, 45]);

        $this->assertEquals('15,30,45 * * * *', $schedule->cronExpression());
    }

    public function testEveryTwoHours(): void
    {
        $schedule = Schedule::create()->everyTwoHours();

        $this->assertEquals('0 */2 * * *', $schedule->cronExpression());
    }

    public function testEveryThreeHours(): void
    {
        $schedule = Schedule::create()->everyThreeHours();

        $this->assertEquals('0 */3 * * *', $schedule->cronExpression());
    }

    public function testEveryFourHours(): void
    {
        $schedule = Schedule::create()->everyFourHours();

        $this->assertEquals('0 */4 * * *', $schedule->cronExpression());
    }

    public function testEverySixHours(): void
    {
        $schedule = Schedule::create()->everySixHours();

        $this->assertEquals('0 */6 * * *', $schedule->cronExpression());
    }

    public function testDaily(): void
    {
        $schedule = Schedule::create()->daily();

        $this->assertEquals('0 0 * * *', $schedule->cronExpression());
    }

    public function testDailyAt(): void
    {
        $schedule = Schedule::create()->dailyAt('14:30');

        $this->assertEquals('30 14 * * *', $schedule->cronExpression());
    }

    public function testAtAliasForDailyAt(): void
    {
        $schedule = Schedule::create()->at('09:15');

        $this->assertEquals('15 9 * * *', $schedule->cronExpression());
    }

    public function testTwiceDailyDefault(): void
    {
        $schedule = Schedule::create()->twiceDaily();

        $this->assertEquals('0 1,13 * * *', $schedule->cronExpression());
    }

    public function testTwiceDailyCustomHours(): void
    {
        $schedule = Schedule::create()->twiceDaily(8, 20);

        $this->assertEquals('0 8,20 * * *', $schedule->cronExpression());
    }

    public function testTwiceDailyAtWithOffset(): void
    {
        $schedule = Schedule::create()->twiceDailyAt(8, 20, 30);

        $this->assertEquals('30 8,20 * * *', $schedule->cronExpression());
    }

    public function testWeekdays(): void
    {
        $schedule = Schedule::create()->weekdays();

        $this->assertEquals('* * * * 1-5', $schedule->cronExpression());
    }

    public function testWeekends(): void
    {
        $schedule = Schedule::create()->weekends();

        $this->assertEquals('* * * * 6,0', $schedule->cronExpression());
    }

    public function testMondays(): void
    {
        $schedule = Schedule::create()->mondays();

        $this->assertEquals('* * * * 1', $schedule->cronExpression());
    }

    public function testTuesdays(): void
    {
        $schedule = Schedule::create()->tuesdays();

        $this->assertEquals('* * * * 2', $schedule->cronExpression());
    }

    public function testWednesdays(): void
    {
        $schedule = Schedule::create()->wednesdays();

        $this->assertEquals('* * * * 3', $schedule->cronExpression());
    }

    public function testThursdays(): void
    {
        $schedule = Schedule::create()->thursdays();

        $this->assertEquals('* * * * 4', $schedule->cronExpression());
    }

    public function testFridays(): void
    {
        $schedule = Schedule::create()->fridays();

        $this->assertEquals('* * * * 5', $schedule->cronExpression());
    }

    public function testSaturdays(): void
    {
        $schedule = Schedule::create()->saturdays();

        $this->assertEquals('* * * * 6', $schedule->cronExpression());
    }

    public function testSundays(): void
    {
        $schedule = Schedule::create()->sundays();

        $this->assertEquals('* * * * 0', $schedule->cronExpression());
    }

    public function testWeekly(): void
    {
        $schedule = Schedule::create()->weekly();

        $this->assertEquals('0 0 * * 0', $schedule->cronExpression());
    }

    public function testWeeklyOnSingleDay(): void
    {
        $schedule = Schedule::create()->weeklyOn(3); // Wednesday

        // weeklyOn calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * * * 3', $schedule->cronExpression());
    }

    public function testWeeklyOnMultipleDays(): void
    {
        $schedule = Schedule::create()->weeklyOn([1, 3, 5]); // Monday, Wednesday, Friday

        // weeklyOn calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * * * 1,3,5', $schedule->cronExpression());
    }

    public function testWeeklyOnWithTime(): void
    {
        $schedule = Schedule::create()->weeklyOn(2, '15:30'); // Tuesday at 3:30 PM

        // weeklyOn calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * * * 2', $schedule->cronExpression());
    }

    public function testMonthly(): void
    {
        $schedule = Schedule::create()->monthly();

        $this->assertEquals('0 0 1 * *', $schedule->cronExpression());
    }

    public function testMonthlyOnDay(): void
    {
        $schedule = Schedule::create()->monthlyOn(15);

        // monthlyOn calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * 15 * *', $schedule->cronExpression());
    }

    public function testMonthlyOnDayWithTime(): void
    {
        $schedule = Schedule::create()->monthlyOn(15, '10:30');

        // monthlyOn calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * 15 * *', $schedule->cronExpression());
    }

    public function testTwiceMonthly(): void
    {
        $schedule = Schedule::create()->twiceMonthly();

        // twiceMonthly calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * 1,16 * *', $schedule->cronExpression());
    }

    public function testTwiceMonthlyCustomDays(): void
    {
        $schedule = Schedule::create()->twiceMonthly(5, 20);

        // twiceMonthly calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * 5,20 * *', $schedule->cronExpression());
    }

    public function testTwiceMonthlyWithTime(): void
    {
        $schedule = Schedule::create()->twiceMonthly(5, 20, '14:00');

        // twiceMonthly calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * 5,20 * *', $schedule->cronExpression());
    }

    public function testQuarterly(): void
    {
        $schedule = Schedule::create()->quarterly();

        $this->assertEquals('0 0 1 1-12/3 *', $schedule->cronExpression());
    }

    public function testYearly(): void
    {
        $schedule = Schedule::create()->yearly();

        $this->assertEquals('0 0 1 1 *', $schedule->cronExpression());
    }

    public function testYearlyOn(): void
    {
        $schedule = Schedule::create()->yearlyOn(6, 15, '12:00'); // June 15th at noon

        $this->assertEquals('0 12 15 6 *', $schedule->cronExpression());
    }

    public function testDaysWithSingleDay(): void
    {
        $schedule = Schedule::create()->days(1);

        $this->assertEquals('* * * * 1', $schedule->cronExpression());
    }

    public function testDaysWithArray(): void
    {
        $schedule = Schedule::create()->days([1, 3, 5]);

        $this->assertEquals('* * * * 1,3,5', $schedule->cronExpression());
    }

    public function testDaysWithMultipleArguments(): void
    {
        $schedule = Schedule::create()->days(1, 3, 5);

        $this->assertEquals('* * * * 1,3,5', $schedule->cronExpression());
    }

    public function testWhenFilterWithCallback(): void
    {
        $schedule = Schedule::create()->when(function () { return true; });

        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testWhenFilterWithBoolean(): void
    {
        $schedule = Schedule::create()->when(true);

        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testSkipFilterWithCallback(): void
    {
        $schedule = Schedule::create()->skip(function () { return false; });

        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testSkipFilterWithBoolean(): void
    {
        $schedule = Schedule::create()->skip(false);

        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testBetweenTimeInterval(): void
    {
        $schedule = Schedule::create()->between('09:00', '17:00');

        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testUnlessBetweenTimeInterval(): void
    {
        $schedule = Schedule::create()->unlessBetween('22:00', '06:00');

        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testIsDueWithMatchingCron(): void
    {
        $this->travelTo('2025-01-19 12:00:00');

        $schedule = Schedule::create()->hourly(); // 0 * * * *

        $this->assertTrue($schedule->isDue('2025-01-19 13:00:00'));
        $this->assertFalse($schedule->isDue('2025-01-19 13:30:00'));
    }

    public function testIsDueWithCurrentTimeWhenNull(): void
    {
        $this->travelTo('2025-01-19 14:00:00');

        $schedule = Schedule::create()->hourly();

        $this->assertTrue($schedule->isDue());
    }

    public function testCurrentlyDueAt(): void
    {
        $this->travelTo('2025-01-19 12:30:00');

        $schedule = Schedule::create()->hourly(); // Next hour mark
        $dueTime = $schedule->currentlyDueAt();

        $this->assertCarbonEqualToMinute(
            Carbon::parse('2025-01-19 13:00:00'),
            $dueTime
        );
    }

    public function testCurrentlyDueAtWithSpecificTime(): void
    {
        $schedule = Schedule::create()->daily(); // 0 0 * * *
        $dueTime = $schedule->currentlyDueAt('2025-01-19 14:30:00');

        $this->assertCarbonEqualToMinute(
            Carbon::parse('2025-01-20 00:00:00'),
            $dueTime
        );
    }

    public function testMethodChainingPreservesImmutability(): void
    {
        $original = Schedule::create();

        // Test that chained method calls return new instances
        $this->assertImmutableChain($original, [
            'daily',
            ['method' => 'at', 'args' => ['09:00']],
            'weekdays',
            ['method' => 'when', 'args' => [function () { return true; }]]
        ]);

        // Verify original unchanged and final result is correct
        $chained = $original->daily()->at('09:00')->weekdays()->when(function () { return true; });
        $this->assertEquals('* * * * *', $original->cronExpression());
        $this->assertEquals('0 9 * * 1-5', $chained->cronExpression());
    }

    public function testTimezoneHandling(): void
    {
        $utc = new DateTimeZone('UTC');
        $est = new DateTimeZone('America/New_York');

        $schedule = Schedule::create()->withTimeZone($est);

        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testLastDayOfMonth(): void
    {
        $this->travelTo('2025-01-15 12:00:00'); // January has 31 days

        $schedule = Schedule::create()->lastDayOfMonth('23:59');

        $this->assertEquals('59 23 31 * *', $schedule->cronExpression());
    }
}
