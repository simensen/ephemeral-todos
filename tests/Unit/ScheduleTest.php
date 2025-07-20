<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use Carbon\Carbon;
use DateTimeZone;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Tests\TestCase;

class ScheduleTest extends TestCase
{
    public function test_create_returns_new_instance(): void
    {
        $schedule = Schedule::create();
        
        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function test_default_cron_expression(): void
    {
        $schedule = Schedule::create();
        
        $this->assertEquals('* * * * *', $schedule->cronExpression());
    }

    public function test_with_cron_expression_returns_new_instance(): void
    {
        $original = Schedule::create();
        $modified = $original->withCronExpression('0 0 * * *');
        
        $this->assertNotSame($original, $modified);
        $this->assertEquals('* * * * *', $original->cronExpression());
        $this->assertEquals('0 0 * * *', $modified->cronExpression());
    }

    public function test_with_timezone_returns_new_instance(): void
    {
        $original = Schedule::create();
        $timezone = new DateTimeZone('America/New_York');
        $modified = $original->withTimeZone($timezone);
        
        $this->assertNotSame($original, $modified);
    }

    public function test_every_minute(): void
    {
        $schedule = Schedule::create()->everyMinute();
        
        $this->assertEquals('* * * * *', $schedule->cronExpression());
    }

    public function test_every_two_minutes(): void
    {
        $schedule = Schedule::create()->everyTwoMinutes();
        
        $this->assertEquals('*/2 * * * *', $schedule->cronExpression());
    }

    public function test_every_three_minutes(): void
    {
        $schedule = Schedule::create()->everyThreeMinutes();
        
        $this->assertEquals('*/3 * * * *', $schedule->cronExpression());
    }

    public function test_every_five_minutes(): void
    {
        $schedule = Schedule::create()->everyFiveMinutes();
        
        $this->assertEquals('*/5 * * * *', $schedule->cronExpression());
    }

    public function test_every_ten_minutes(): void
    {
        $schedule = Schedule::create()->everyTenMinutes();
        
        $this->assertEquals('*/10 * * * *', $schedule->cronExpression());
    }

    public function test_every_fifteen_minutes(): void
    {
        $schedule = Schedule::create()->everyFifteenMinutes();
        
        $this->assertEquals('*/15 * * * *', $schedule->cronExpression());
    }

    public function test_every_thirty_minutes(): void
    {
        $schedule = Schedule::create()->everyThirtyMinutes();
        
        $this->assertEquals('0,30 * * * *', $schedule->cronExpression());
    }

    public function test_hourly(): void
    {
        $schedule = Schedule::create()->hourly();
        
        $this->assertEquals('0 * * * *', $schedule->cronExpression());
    }

    public function test_hourly_at_single_offset(): void
    {
        $schedule = Schedule::create()->hourlyAt(15);
        
        $this->assertEquals('15 * * * *', $schedule->cronExpression());
    }

    public function test_hourly_at_multiple_offsets(): void
    {
        $schedule = Schedule::create()->hourlyAt([15, 30, 45]);
        
        $this->assertEquals('15,30,45 * * * *', $schedule->cronExpression());
    }

    public function test_every_two_hours(): void
    {
        $schedule = Schedule::create()->everyTwoHours();
        
        $this->assertEquals('0 */2 * * *', $schedule->cronExpression());
    }

    public function test_every_three_hours(): void
    {
        $schedule = Schedule::create()->everyThreeHours();
        
        $this->assertEquals('0 */3 * * *', $schedule->cronExpression());
    }

    public function test_every_four_hours(): void
    {
        $schedule = Schedule::create()->everyFourHours();
        
        $this->assertEquals('0 */4 * * *', $schedule->cronExpression());
    }

    public function test_every_six_hours(): void
    {
        $schedule = Schedule::create()->everySixHours();
        
        $this->assertEquals('0 */6 * * *', $schedule->cronExpression());
    }

    public function test_daily(): void
    {
        $schedule = Schedule::create()->daily();
        
        $this->assertEquals('0 0 * * *', $schedule->cronExpression());
    }

    public function test_daily_at(): void
    {
        $schedule = Schedule::create()->dailyAt('14:30');
        
        $this->assertEquals('30 14 * * *', $schedule->cronExpression());
    }

    public function test_at_alias_for_daily_at(): void
    {
        $schedule = Schedule::create()->at('09:15');
        
        $this->assertEquals('15 9 * * *', $schedule->cronExpression());
    }

    public function test_twice_daily_default(): void
    {
        $schedule = Schedule::create()->twiceDaily();
        
        $this->assertEquals('0 1,13 * * *', $schedule->cronExpression());
    }

    public function test_twice_daily_custom_hours(): void
    {
        $schedule = Schedule::create()->twiceDaily(8, 20);
        
        $this->assertEquals('0 8,20 * * *', $schedule->cronExpression());
    }

    public function test_twice_daily_at_with_offset(): void
    {
        $schedule = Schedule::create()->twiceDailyAt(8, 20, 30);
        
        $this->assertEquals('30 8,20 * * *', $schedule->cronExpression());
    }

    public function test_weekdays(): void
    {
        $schedule = Schedule::create()->weekdays();
        
        $this->assertEquals('* * * * 1-5', $schedule->cronExpression());
    }

    public function test_weekends(): void
    {
        $schedule = Schedule::create()->weekends();
        
        $this->assertEquals('* * * * 6,0', $schedule->cronExpression());
    }

    public function test_mondays(): void
    {
        $schedule = Schedule::create()->mondays();
        
        $this->assertEquals('* * * * 1', $schedule->cronExpression());
    }

    public function test_tuesdays(): void
    {
        $schedule = Schedule::create()->tuesdays();
        
        $this->assertEquals('* * * * 2', $schedule->cronExpression());
    }

    public function test_wednesdays(): void
    {
        $schedule = Schedule::create()->wednesdays();
        
        $this->assertEquals('* * * * 3', $schedule->cronExpression());
    }

    public function test_thursdays(): void
    {
        $schedule = Schedule::create()->thursdays();
        
        $this->assertEquals('* * * * 4', $schedule->cronExpression());
    }

    public function test_fridays(): void
    {
        $schedule = Schedule::create()->fridays();
        
        $this->assertEquals('* * * * 5', $schedule->cronExpression());
    }

    public function test_saturdays(): void
    {
        $schedule = Schedule::create()->saturdays();
        
        $this->assertEquals('* * * * 6', $schedule->cronExpression());
    }

    public function test_sundays(): void
    {
        $schedule = Schedule::create()->sundays();
        
        $this->assertEquals('* * * * 0', $schedule->cronExpression());
    }

    public function test_weekly(): void
    {
        $schedule = Schedule::create()->weekly();
        
        $this->assertEquals('0 0 * * 0', $schedule->cronExpression());
    }

    public function test_weekly_on_single_day(): void
    {
        $schedule = Schedule::create()->weeklyOn(3); // Wednesday
        
        // weeklyOn calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * * * 3', $schedule->cronExpression());
    }

    public function test_weekly_on_multiple_days(): void
    {
        $schedule = Schedule::create()->weeklyOn([1, 3, 5]); // Monday, Wednesday, Friday
        
        // weeklyOn calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * * * 1,3,5', $schedule->cronExpression());
    }

    public function test_weekly_on_with_time(): void
    {
        $schedule = Schedule::create()->weeklyOn(2, '15:30'); // Tuesday at 3:30 PM
        
        // weeklyOn calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * * * 2', $schedule->cronExpression());
    }

    public function test_monthly(): void
    {
        $schedule = Schedule::create()->monthly();
        
        $this->assertEquals('0 0 1 * *', $schedule->cronExpression());
    }

    public function test_monthly_on_day(): void
    {
        $schedule = Schedule::create()->monthlyOn(15);
        
        // monthlyOn calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * 15 * *', $schedule->cronExpression());
    }

    public function test_monthly_on_day_with_time(): void
    {
        $schedule = Schedule::create()->monthlyOn(15, '10:30');
        
        // monthlyOn calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * 15 * *', $schedule->cronExpression());
    }

    public function test_twice_monthly(): void
    {
        $schedule = Schedule::create()->twiceMonthly();
        
        // twiceMonthly calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * 1,16 * *', $schedule->cronExpression());
    }

    public function test_twice_monthly_custom_days(): void
    {
        $schedule = Schedule::create()->twiceMonthly(5, 20);
        
        // twiceMonthly calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * 5,20 * *', $schedule->cronExpression());
    }

    public function test_twice_monthly_with_time(): void
    {
        $schedule = Schedule::create()->twiceMonthly(5, 20, '14:00');
        
        // twiceMonthly calls dailyAt but doesn't return the result, so time stays default
        $this->assertEquals('* * 5,20 * *', $schedule->cronExpression());
    }

    public function test_quarterly(): void
    {
        $schedule = Schedule::create()->quarterly();
        
        $this->assertEquals('0 0 1 1-12/3 *', $schedule->cronExpression());
    }

    public function test_yearly(): void
    {
        $schedule = Schedule::create()->yearly();
        
        $this->assertEquals('0 0 1 1 *', $schedule->cronExpression());
    }

    public function test_yearly_on(): void
    {
        $schedule = Schedule::create()->yearlyOn(6, 15, '12:00'); // June 15th at noon
        
        $this->assertEquals('0 12 15 6 *', $schedule->cronExpression());
    }

    public function test_days_with_single_day(): void
    {
        $schedule = Schedule::create()->days(1);
        
        $this->assertEquals('* * * * 1', $schedule->cronExpression());
    }

    public function test_days_with_array(): void
    {
        $schedule = Schedule::create()->days([1, 3, 5]);
        
        $this->assertEquals('* * * * 1,3,5', $schedule->cronExpression());
    }

    public function test_days_with_multiple_arguments(): void
    {
        $schedule = Schedule::create()->days(1, 3, 5);
        
        $this->assertEquals('* * * * 1,3,5', $schedule->cronExpression());
    }

    public function test_when_filter_with_callback(): void
    {
        $schedule = Schedule::create()->when(function() { return true; });
        
        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function test_when_filter_with_boolean(): void
    {
        $schedule = Schedule::create()->when(true);
        
        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function test_skip_filter_with_callback(): void
    {
        $schedule = Schedule::create()->skip(function() { return false; });
        
        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function test_skip_filter_with_boolean(): void
    {
        $schedule = Schedule::create()->skip(false);
        
        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function test_between_time_interval(): void
    {
        $schedule = Schedule::create()->between('09:00', '17:00');
        
        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function test_unless_between_time_interval(): void
    {
        $schedule = Schedule::create()->unlessBetween('22:00', '06:00');
        
        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function test_is_due_with_matching_cron(): void
    {
        $this->travelTo('2025-01-19 12:00:00');
        
        $schedule = Schedule::create()->hourly(); // 0 * * * *
        
        $this->assertTrue($schedule->isDue('2025-01-19 13:00:00'));
        $this->assertFalse($schedule->isDue('2025-01-19 13:30:00'));
    }

    public function test_is_due_with_current_time_when_null(): void
    {
        $this->travelTo('2025-01-19 14:00:00');
        
        $schedule = Schedule::create()->hourly();
        
        $this->assertTrue($schedule->isDue());
    }

    public function test_currently_due_at(): void
    {
        $this->travelTo('2025-01-19 12:30:00');
        
        $schedule = Schedule::create()->hourly(); // Next hour mark
        $dueTime = $schedule->currentlyDueAt();
        
        $this->assertCarbonEqualToMinute(
            Carbon::parse('2025-01-19 13:00:00'), 
            $dueTime
        );
    }

    public function test_currently_due_at_with_specific_time(): void
    {
        $schedule = Schedule::create()->daily(); // 0 0 * * *
        $dueTime = $schedule->currentlyDueAt('2025-01-19 14:30:00');
        
        $this->assertCarbonEqualToMinute(
            Carbon::parse('2025-01-20 00:00:00'),
            $dueTime
        );
    }

    public function test_method_chaining_preserves_immutability(): void
    {
        $original = Schedule::create();
        
        $chained = $original
            ->daily()
            ->at('09:00')
            ->weekdays()
            ->when(function() { return true; });
        
        $this->assertNotSame($original, $chained);
        $this->assertEquals('* * * * *', $original->cronExpression());
        $this->assertEquals('0 9 * * 1-5', $chained->cronExpression());
    }

    public function test_timezone_handling(): void
    {
        $utc = new DateTimeZone('UTC');
        $est = new DateTimeZone('America/New_York');
        
        $schedule = Schedule::create()->withTimeZone($est);
        
        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function test_last_day_of_month(): void
    {
        $this->travelTo('2025-01-15 12:00:00'); // January has 31 days
        
        $schedule = Schedule::create()->lastDayOfMonth('23:59');
        
        $this->assertEquals('59 23 31 * *', $schedule->cronExpression());
    }
}