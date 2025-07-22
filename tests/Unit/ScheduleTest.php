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

    // NOTE: Individual schedule method tests have been replaced by the parameterized
    // testScheduleMethodCronExpressions method using the scheduleMethodsProvider data provider.
    // This eliminates code duplication while maintaining comprehensive test coverage.

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

    // NOTE: testLastDayOfMonth is now covered by the parameterized test via scheduleMethodsProvider

    /**
     * Data provider for schedule method to cron expression testing.
     * 
     * @return array Array of [method_description, method_call_closure, expected_cron_expression]
     */
    public static function scheduleMethodsProvider(): array
    {
        return [
            // Basic time intervals
            ['everyMinute', fn($schedule) => $schedule->everyMinute(), '* * * * *'],
            ['everyTwoMinutes', fn($schedule) => $schedule->everyTwoMinutes(), '*/2 * * * *'],
            ['everyThreeMinutes', fn($schedule) => $schedule->everyThreeMinutes(), '*/3 * * * *'],
            ['everyFiveMinutes', fn($schedule) => $schedule->everyFiveMinutes(), '*/5 * * * *'],
            ['everyTenMinutes', fn($schedule) => $schedule->everyTenMinutes(), '*/10 * * * *'],
            ['everyFifteenMinutes', fn($schedule) => $schedule->everyFifteenMinutes(), '*/15 * * * *'],
            ['everyThirtyMinutes', fn($schedule) => $schedule->everyThirtyMinutes(), '0,30 * * * *'],

            // Hourly intervals
            ['hourly', fn($schedule) => $schedule->hourly(), '0 * * * *'],
            ['hourlyAt with single offset', fn($schedule) => $schedule->hourlyAt(15), '15 * * * *'],
            ['hourlyAt with multiple offsets', fn($schedule) => $schedule->hourlyAt([15, 30, 45]), '15,30,45 * * * *'],
            ['everyTwoHours', fn($schedule) => $schedule->everyTwoHours(), '0 */2 * * *'],
            ['everyThreeHours', fn($schedule) => $schedule->everyThreeHours(), '0 */3 * * *'],
            ['everyFourHours', fn($schedule) => $schedule->everyFourHours(), '0 */4 * * *'],
            ['everySixHours', fn($schedule) => $schedule->everySixHours(), '0 */6 * * *'],

            // Daily intervals
            ['daily', fn($schedule) => $schedule->daily(), '0 0 * * *'],
            ['dailyAt', fn($schedule) => $schedule->dailyAt('14:30'), '30 14 * * *'],
            ['at (alias for dailyAt)', fn($schedule) => $schedule->at('09:15'), '15 9 * * *'],
            ['twiceDaily default', fn($schedule) => $schedule->twiceDaily(), '0 1,13 * * *'],
            ['twiceDaily custom hours', fn($schedule) => $schedule->twiceDaily(8, 20), '0 8,20 * * *'],
            ['twiceDailyAt with offset', fn($schedule) => $schedule->twiceDailyAt(8, 20, 30), '30 8,20 * * *'],

            // Weekly day selections
            ['weekdays', fn($schedule) => $schedule->weekdays(), '* * * * 1-5'],
            ['weekends', fn($schedule) => $schedule->weekends(), '* * * * 6,0'],
            ['mondays', fn($schedule) => $schedule->mondays(), '* * * * 1'],
            ['tuesdays', fn($schedule) => $schedule->tuesdays(), '* * * * 2'],
            ['wednesdays', fn($schedule) => $schedule->wednesdays(), '* * * * 3'],
            ['thursdays', fn($schedule) => $schedule->thursdays(), '* * * * 4'],
            ['fridays', fn($schedule) => $schedule->fridays(), '* * * * 5'],
            ['saturdays', fn($schedule) => $schedule->saturdays(), '* * * * 6'],
            ['sundays', fn($schedule) => $schedule->sundays(), '* * * * 0'],

            // Weekly intervals
            ['weekly', fn($schedule) => $schedule->weekly(), '0 0 * * 0'],
            ['weeklyOn single day', fn($schedule) => $schedule->weeklyOn(3), '* * * * 3'],
            ['weeklyOn multiple days', fn($schedule) => $schedule->weeklyOn([1, 3, 5]), '* * * * 1,3,5'],
            ['weeklyOn with time', fn($schedule) => $schedule->weeklyOn(2, '15:30'), '* * * * 2'],

            // Monthly intervals
            ['monthly', fn($schedule) => $schedule->monthly(), '0 0 1 * *'],
            ['monthlyOn day', fn($schedule) => $schedule->monthlyOn(15), '* * 15 * *'],
            ['monthlyOn day with time', fn($schedule) => $schedule->monthlyOn(15, '10:30'), '* * 15 * *'],
            ['twiceMonthly default', fn($schedule) => $schedule->twiceMonthly(), '* * 1,16 * *'],
            ['twiceMonthly custom days', fn($schedule) => $schedule->twiceMonthly(5, 20), '* * 5,20 * *'],
            ['twiceMonthly with time', fn($schedule) => $schedule->twiceMonthly(5, 20, '14:00'), '* * 5,20 * *'],

            // Yearly intervals
            ['quarterly', fn($schedule) => $schedule->quarterly(), '0 0 1 1-12/3 *'],
            ['yearly', fn($schedule) => $schedule->yearly(), '0 0 1 1 *'],
            ['yearlyOn', fn($schedule) => $schedule->yearlyOn(6, 15, '12:00'), '0 12 15 6 *'],

            // Days method variations
            ['days with single day', fn($schedule) => $schedule->days(1), '* * * * 1'],
            ['days with array', fn($schedule) => $schedule->days([1, 3, 5]), '* * * * 1,3,5'],
            ['days with multiple arguments', fn($schedule) => $schedule->days(1, 3, 5), '* * * * 1,3,5'],

            // Special cases
            ['lastDayOfMonth', fn($schedule) => $schedule->lastDayOfMonth('23:59'), '59 23 31 * *'],
        ];
    }

    /**
     * Test that schedule convenience methods produce expected cron expressions.
     * 
     * @dataProvider scheduleMethodsProvider
     */
    public function testScheduleMethodCronExpressions(string $description, callable $scheduleCall, string $expectedCronExpression): void
    {
        $schedule = Schedule::create();
        $configuredSchedule = $scheduleCall($schedule);
        
        $this->assertEquals($expectedCronExpression, $configuredSchedule->cronExpression(), 
            "Schedule method '{$description}' should produce cron expression '{$expectedCronExpression}'");
    }
}
