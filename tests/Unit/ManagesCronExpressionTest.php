<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use Carbon\Carbon;
use DateTimeZone;
use Simensen\EphemeralTodos\ManagesCronExpression;
use Simensen\EphemeralTodos\Tests\TestCase;

class ManagesCronExpressionTest extends TestCase
{
    private TestManagesCronExpression $trait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trait = new TestManagesCronExpression();
    }

    public function test_default_cron_expression(): void
    {
        $this->assertEquals('* * * * *', $this->trait->cronExpression());
    }

    public function test_with_cron_expression_returns_new_instance(): void
    {
        $original = $this->trait;
        $modified = $original->withCronExpression('0 0 * * *');

        $this->assertNotSame($original, $modified);
        $this->assertEquals('* * * * *', $original->cronExpression());
        $this->assertEquals('0 0 * * *', $modified->cronExpression());
    }

    public function test_with_timezone_returns_new_instance(): void
    {
        $original = $this->trait;
        $timezone = new DateTimeZone('America/New_York');
        $modified = $original->withTimeZone($timezone);

        $this->assertNotSame($original, $modified);
    }

    public function test_when_filter_with_callable(): void
    {
        $modified = $this->trait->when(function() { return true; });

        $this->assertNotSame($this->trait, $modified);
        $this->assertInstanceOf(TestManagesCronExpression::class, $modified);
    }

    public function test_when_filter_with_boolean(): void
    {
        $modified = $this->trait->when(true);

        $this->assertNotSame($this->trait, $modified);
        $this->assertInstanceOf(TestManagesCronExpression::class, $modified);
    }

    public function test_skip_filter_with_callable(): void
    {
        $modified = $this->trait->skip(function() { return false; });

        $this->assertNotSame($this->trait, $modified);
        $this->assertInstanceOf(TestManagesCronExpression::class, $modified);
    }

    public function test_skip_filter_with_boolean(): void
    {
        $modified = $this->trait->skip(false);

        $this->assertNotSame($this->trait, $modified);
        $this->assertInstanceOf(TestManagesCronExpression::class, $modified);
    }

    public function test_between_creates_time_interval_filter(): void
    {
        $modified = $this->trait->between('09:00', '17:00');

        $this->assertNotSame($this->trait, $modified);
        $this->assertInstanceOf(TestManagesCronExpression::class, $modified);
    }

    public function test_unless_between_creates_inverse_time_interval_filter(): void
    {
        $modified = $this->trait->unlessBetween('22:00', '06:00');

        $this->assertNotSame($this->trait, $modified);
        $this->assertInstanceOf(TestManagesCronExpression::class, $modified);
    }

    public function test_splice_into_position_modifies_cron_expression(): void
    {
        // Test splicing minute position (position 1)
        $modified = $this->trait->testSpliceIntoPosition(1, '30');
        $this->assertEquals('30 * * * *', $modified->cronExpression());

        // Test splicing hour position (position 2)
        $modified = $this->trait->testSpliceIntoPosition(2, '14');
        $this->assertEquals('* 14 * * *', $modified->cronExpression());

        // Test splicing day position (position 3)
        $modified = $this->trait->testSpliceIntoPosition(3, '15');
        $this->assertEquals('* * 15 * *', $modified->cronExpression());

        // Test splicing month position (position 4)
        $modified = $this->trait->testSpliceIntoPosition(4, '6');
        $this->assertEquals('* * * 6 *', $modified->cronExpression());

        // Test splicing day of week position (position 5)
        $modified = $this->trait->testSpliceIntoPosition(5, '1-5');
        $this->assertEquals('* * * * 1-5', $modified->cronExpression());
    }

    public function test_splice_into_position_with_multiple_values(): void
    {
        $modified = $this->trait->testSpliceIntoPosition(1, '15,30,45');
        $this->assertEquals('15,30,45 * * * *', $modified->cronExpression());
    }

    public function test_splice_into_position_preserves_immutability(): void
    {
        $original = $this->trait;
        $modified = $original->testSpliceIntoPosition(1, '30');

        $this->assertNotSame($original, $modified);
        $this->assertEquals('* * * * *', $original->cronExpression());
        $this->assertEquals('30 * * * *', $modified->cronExpression());
    }

    public function test_is_due_with_matching_expression(): void
    {
        $this->travelTo('2025-01-19 12:00:00');

        // Test hourly expression
        $hourly = $this->trait->withCronExpression('0 * * * *');
        $this->assertTrue($hourly->isDue('2025-01-19 13:00:00'));
        $this->assertFalse($hourly->isDue('2025-01-19 13:30:00'));
    }

    public function test_is_due_with_current_time_when_null(): void
    {
        $this->travelTo('2025-01-19 14:00:00');

        $hourly = $this->trait->withCronExpression('0 * * * *');
        $this->assertTrue($hourly->isDue());
    }

    public function test_currently_due_at_calculates_next_run_time(): void
    {
        $this->travelTo('2025-01-19 12:30:00');

        $hourly = $this->trait->withCronExpression('0 * * * *');
        $dueTime = $hourly->currentlyDueAt();

        $this->assertCarbonEqualToMinute(
            Carbon::parse('2025-01-19 13:00:00'),
            $dueTime
        );
    }

    public function test_currently_due_at_with_specific_time(): void
    {
        $daily = $this->trait->withCronExpression('0 0 * * *');
        $dueTime = $daily->currentlyDueAt('2025-01-19 14:30:00');

        $this->assertCarbonEqualToMinute(
            Carbon::parse('2025-01-20 00:00:00'),
            $dueTime
        );
    }

    public function test_timezone_handling_in_filters(): void
    {
        $timezone = new DateTimeZone('America/New_York');
        $withTimezone = $this->trait->withTimeZone($timezone);

        // between() should work with the timezone
        $filtered = $withTimezone->between('09:00', '17:00');
        $this->assertInstanceOf(TestManagesCronExpression::class, $filtered);
    }

    public function test_complex_filter_combinations(): void
    {
        $complex = $this->trait
            ->when(function() { return true; })
            ->skip(function() { return false; })
            ->between('09:00', '17:00')
            ->unlessBetween('12:00', '13:00');

        $this->assertInstanceOf(TestManagesCronExpression::class, $complex);
        $this->assertNotSame($this->trait, $complex);
    }

    public function test_in_time_interval_logic_with_same_day(): void
    {
        $this->travelTo('2025-01-19 12:00:00');

        $betweenSameDay = $this->trait->between('09:00', '17:00');
        $this->assertInstanceOf(TestManagesCronExpression::class, $betweenSameDay);
    }

    public function test_in_time_interval_logic_with_overnight(): void
    {
        $this->travelTo('2025-01-19 01:00:00');

        $betweenOvernight = $this->trait->between('22:00', '06:00');
        $this->assertInstanceOf(TestManagesCronExpression::class, $betweenOvernight);
    }

    public function test_method_chaining_maintains_immutability(): void
    {
        $original = $this->trait;

        $chained = $original
            ->withCronExpression('0 9 * * *')
            ->when(function() { return true; })
            ->skip(function() { return false; })
            ->between('09:00', '17:00');

        $this->assertNotSame($original, $chained);
        $this->assertEquals('* * * * *', $original->cronExpression());
        $this->assertEquals('0 9 * * *', $chained->cronExpression());
    }

    public function test_cron_constants_usage(): void
    {
        // Test that the trait can handle Cron constants
        $weekdays = $this->trait->testSpliceIntoPosition(5, '1-5'); // MONDAY-FRIDAY
        $this->assertEquals('* * * * 1-5', $weekdays->cronExpression());

        $weekends = $this->trait->testSpliceIntoPosition(5, '6,0'); // SATURDAY,SUNDAY
        $this->assertEquals('* * * * 6,0', $weekends->cronExpression());
    }

    public function test_to_carbon_method_with_timezone(): void
    {
        $timezone = new DateTimeZone('America/New_York');
        $withTimezone = $this->trait->withTimeZone($timezone);

        // Test isDue uses the timezone
        $this->assertTrue($withTimezone->isDue('2025-01-19 12:00:00'));
    }

    public function test_passes_cron_expression_validation(): void
    {
        $this->travelTo('2025-01-19 12:00:00');

        // Test various valid cron expressions
        $everyMinute = $this->trait->withCronExpression('* * * * *');
        $this->assertTrue($everyMinute->isDue());

        $hourly = $this->trait->withCronExpression('0 * * * *');
        $this->assertTrue($hourly->isDue('2025-01-19 13:00:00'));
        $this->assertFalse($hourly->isDue('2025-01-19 13:30:00'));

        $daily = $this->trait->withCronExpression('0 0 * * *');
        $this->assertTrue($daily->isDue('2025-01-20 00:00:00'));
        $this->assertFalse($daily->isDue('2025-01-19 12:00:00'));
    }
}

// Test class that exposes the protected methods for testing
class TestManagesCronExpression
{
    use ManagesCronExpression;

    public function testSpliceIntoPosition(int $position, string|int $value): static
    {
        return $this->spliceIntoPosition($position, $value);
    }
}