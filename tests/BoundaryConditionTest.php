<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\BeforeDueBy;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\In;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Todo;
use Simensen\EphemeralTodos\Todos;
use Simensen\EphemeralTodos\Utils;

class BoundaryConditionTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow('2024-01-15 10:00:00 UTC');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function test_minute_boundary_precision()
    {
        $todos = new Todos();
        
        $todos->define(Definition::define()
            ->withName('Minute Boundary Test')
            ->due(Schedule::create()->daily()->at('14:30')));
        
        // Test exactly at the boundary
        $exactTime = Carbon::parse('2024-01-15 14:30:00.000000');
        $this->assertCount(1, $todos->readyToBeCreatedAt($exactTime));
        
        // Test microseconds before
        $justBefore = Carbon::parse('2024-01-15 14:29:59.999999');
        $this->assertCount(0, $todos->readyToBeCreatedAt($justBefore));
        
        // Test microseconds after (still same minute)
        $justAfter = Carbon::parse('2024-01-15 14:30:00.000001');
        $this->assertCount(1, $todos->readyToBeCreatedAt($justAfter));
        
        // Test next minute boundary
        $nextMinute = Carbon::parse('2024-01-15 14:31:00.000000');
        $this->assertCount(0, $todos->readyToBeCreatedAt($nextMinute));
    }

    public function test_day_boundary_transitions()
    {
        $todos = new Todos();
        
        // Daily task at midnight
        $todos->define(Definition::define()
            ->withName('Midnight Task')
            ->due(Schedule::create()->daily()->at('00:00')));
        
        // Test last second of previous day
        $lastSecond = Carbon::parse('2024-01-15 23:59:59');
        $this->assertCount(0, $todos->readyToBeCreatedAt($lastSecond));
        
        // Test exact midnight
        $midnight = Carbon::parse('2024-01-16 00:00:00');
        $this->assertCount(1, $todos->readyToBeCreatedAt($midnight));
        
        // Test first second of new day
        $firstSecond = Carbon::parse('2024-01-16 00:00:01');
        $this->assertCount(1, $todos->readyToBeCreatedAt($firstSecond));
        
        // Test end of minute after midnight
        $endOfMinute = Carbon::parse('2024-01-16 00:00:59');
        $this->assertCount(1, $todos->readyToBeCreatedAt($endOfMinute));
        
        // Test next minute
        $nextMinute = Carbon::parse('2024-01-16 00:01:00');
        $this->assertCount(0, $todos->readyToBeCreatedAt($nextMinute));
    }

    public function test_month_boundary_edge_cases()
    {
        $todos = new Todos();
        
        // Monthly task on the 1st
        $todos->define(Definition::define()
            ->withName('First of Month')
            ->due(Schedule::create()->monthlyOn(1, '09:00')));
        
        // Test last day of January
        $lastDayJan = Carbon::parse('2024-01-31 09:00:00');
        $this->assertCount(0, $todos->readyToBeCreatedAt($lastDayJan));
        
        // Test first day of February
        $firstDayFeb = Carbon::parse('2024-02-01 09:00:00');
        $this->assertCount(1, $todos->readyToBeCreatedAt($firstDayFeb));
        
        // Test leap year February to March
        $lastDayFeb = Carbon::parse('2024-02-29 09:00:00'); // 2024 is leap year
        $this->assertCount(0, $todos->readyToBeCreatedAt($lastDayFeb));
        
        $firstDayMar = Carbon::parse('2024-03-01 09:00:00');
        $this->assertCount(1, $todos->readyToBeCreatedAt($firstDayMar));
    }

    public function test_week_boundary_transitions()
    {
        $todos = new Todos();
        
        // Weekly Monday task
        $todos->define(Definition::define()
            ->withName('Monday Task')
            ->due(Schedule::create()->weekly()->mondays()->at('08:00')));
        
        // Test Sunday before (day 0)
        $sunday = Carbon::parse('2024-01-14 08:00:00'); // Sunday
        $this->assertCount(0, $todos->readyToBeCreatedAt($sunday));
        
        // Test Monday (day 1)
        $monday = Carbon::parse('2024-01-15 08:00:00'); // Monday
        $this->assertCount(1, $todos->readyToBeCreatedAt($monday));
        
        // Test Tuesday after (day 2)
        $tuesday = Carbon::parse('2024-01-16 08:00:00'); // Tuesday
        $this->assertCount(0, $todos->readyToBeCreatedAt($tuesday));
    }

    public function test_year_boundary_calculations()
    {
        $todos = new Todos();
        
        // Daily task
        $todos->define(Definition::define()
            ->withName('Year Boundary Task')
            ->due(Schedule::create()->daily()->at('23:00')));
        
        // Test New Year's Eve
        $newYearsEve = Carbon::parse('2024-12-31 23:00:00');
        $this->assertCount(1, $todos->readyToBeCreatedAt($newYearsEve));
        
        // Test New Year's Day
        $newYearsDay = Carbon::parse('2025-01-01 23:00:00');
        $this->assertCount(1, $todos->readyToBeCreatedAt($newYearsDay));
        
        // Both should work identically despite year change
        $nyeTodo = $todos->nextInstances($newYearsEve)[0];
        $nydTodo = $todos->nextInstances($newYearsDay)[0];
        
        $this->assertEquals($nyeTodo->name(), $nydTodo->name());
        $this->assertEquals($nyeTodo->priority(), $nydTodo->priority());
    }

    public function test_deletion_timing_boundary_conditions()
    {
        $todos = new Todos();
        
        $todos->define(Definition::define()
            ->withName('Deletion Boundary Test')
            ->due(Schedule::create()->daily()->at('12:00'))
            ->automaticallyDelete(AfterDueBy::oneHour()));
        
        $dueTime = Carbon::parse('2024-01-15 12:00:00');
        $todoInstance = $todos->nextInstances($dueTime)[0];
        
        $deletionTime = $todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt();
        $expectedDeletion = Carbon::parse('2024-01-15 13:00:00');
        
        $this->assertEquals($expectedDeletion, $deletionTime);
        
        // Test precision - should be exactly one hour
        $this->assertEquals(3600, Carbon::instance($dueTime)->diffInSeconds(Carbon::instance($deletionTime)));
    }

    public function test_creation_before_due_boundary()
    {
        $todos = new Todos();
        
        $todos->define(Definition::define()
            ->withName('Before Due Test')
            ->create(BeforeDueBy::thirtyMinutes())
            ->due(Schedule::create()->daily()->at('15:00')));
        
        // Should create at 14:30
        $createTime = Carbon::parse('2024-01-15 14:30:00');
        $this->assertCount(1, $todos->readyToBeCreatedAt($createTime));
        
        // Should not create at 14:29
        $tooEarly = Carbon::parse('2024-01-15 14:29:00');
        $this->assertCount(0, $todos->readyToBeCreatedAt($tooEarly));
        
        // Should not create at due time
        $dueTime = Carbon::parse('2024-01-15 15:00:00');
        $this->assertCount(0, $todos->readyToBeCreatedAt($dueTime));
        
        $todoInstance = $todos->nextInstances($createTime)[0];
        
        $this->assertEquals($createTime, $todoInstance->createAt());
        $this->assertEquals(
            Carbon::parse('2024-01-15 15:00:00'),
            $todoInstance->dueAt()
        );
    }

    public function test_utils_equal_to_minute_boundary_conditions()
    {
        // Test exact minute boundaries
        $time1 = Carbon::parse('2024-01-15 10:30:00.000000');
        $time2 = Carbon::parse('2024-01-15 10:30:59.999999');
        $time3 = Carbon::parse('2024-01-15 10:31:00.000000');
        
        // Same minute
        $this->assertTrue(Utils::equalToTheMinute($time1, $time2));
        
        // Different minutes
        $this->assertFalse(Utils::equalToTheMinute($time2, $time3));
        $this->assertFalse(Utils::equalToTheMinute($time1, $time3));
        
        // Test with millisecond precision
        $time4 = Carbon::parse('2024-01-15 10:30:30.123');
        $time5 = Carbon::parse('2024-01-15 10:30:30.789');
        
        $this->assertTrue(Utils::equalToTheMinute($time4, $time5));
    }

    public function test_extreme_time_values()
    {
        $todos = new Todos();
        
        // Task with very long deletion time
        $todos->define(Definition::define()
            ->withName('Long Deletion Test')
            ->due(Schedule::create()->daily()->at('10:00'))
            ->automaticallyDelete(AfterExistingFor::threeWeeks()));
        
        $todoInstance = $todos->nextInstances(Carbon::parse('2024-01-15 10:00:00'))[0];
        $deletionTime = $todoInstance->automaticallyDeleteWhenCompleteAndAfterExistingAt();
        
        // Should be exactly 3 weeks later
        $expectedDeletion = Carbon::parse('2024-01-15 10:00:00')->addWeeks(3);
        $this->assertEquals($expectedDeletion, $deletionTime);
        
        // Verify it's exactly 21 days
        $this->assertEquals(21, Carbon::parse('2024-01-15 10:00:00')->diffInDays(Carbon::instance($deletionTime)));
    }

    public function test_zero_time_boundaries()
    {
        $todos = new Todos();
        
        // Task with zero-second relative time
        $todos->define(Definition::define()
            ->withName('Zero Time Test')
            ->create(BeforeDueBy::zeroSeconds())
            ->due(Schedule::create()->daily()->at('16:00')));
        
        // Should create exactly at due time
        $dueTime = Carbon::parse('2024-01-15 16:00:00');
        $this->assertCount(1, $todos->readyToBeCreatedAt($dueTime));
        
        $todoInstance = $todos->nextInstances($dueTime)[0];
        
        // Create and due times should be identical
        $this->assertEquals($todoInstance->createAt(), $todoInstance->dueAt());
    }

    public function test_multiple_boundary_crossings()
    {
        $todos = new Todos();
        
        // Task that crosses multiple boundaries
        $todos->define(Definition::define()
            ->withName('Multi Boundary Test')
            ->create(BeforeDueBy::twoHours())
            ->due(Schedule::create()->daily()->at('01:00')) // Early morning
            ->automaticallyDelete(AfterDueBy::oneDay()));
        
        // Should create at 23:00 previous day
        $createTime = Carbon::parse('2024-01-14 23:00:00');
        $this->assertCount(1, $todos->readyToBeCreatedAt($createTime));
        
        $todoInstance = $todos->nextInstances($createTime)[0];
        
        // Verify all time relationships across day boundary
        $this->assertEquals(
            Carbon::parse('2024-01-14 23:00:00'),
            $todoInstance->createAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-15 01:00:00'),
            $todoInstance->dueAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-16 01:00:00'),
            $todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt()
        );
    }

    public function test_weekend_weekday_boundaries()
    {
        $schedule = Schedule::create()->withCronExpression('0 9 * * 1-5'); // Weekdays only
        
        // Test Friday (last weekday)
        $friday = Carbon::parse('2024-01-19 09:00:00'); // Friday
        $this->assertTrue($schedule->isDue($friday));
        
        // Test Saturday (first weekend day)
        $saturday = Carbon::parse('2024-01-20 09:00:00'); // Saturday
        $this->assertFalse($schedule->isDue($saturday));
        
        // Test Sunday (second weekend day)
        $sunday = Carbon::parse('2024-01-21 09:00:00'); // Sunday
        $this->assertFalse($schedule->isDue($sunday));
        
        // Test Monday (first weekday again)
        $monday = Carbon::parse('2024-01-22 09:00:00'); // Monday
        $this->assertTrue($schedule->isDue($monday));
    }

    public function test_hour_minute_second_boundaries()
    {
        $todos = new Todos();
        
        // Task at exact hour
        $todos->define(Definition::define()
            ->withName('Hour Boundary')
            ->due(Schedule::create()->daily()->at('15:00')));
        
        // Test hour transitions
        $beforeHour = Carbon::parse('2024-01-15 14:59:59');
        $exactHour = Carbon::parse('2024-01-15 15:00:00');
        $afterHour = Carbon::parse('2024-01-15 15:00:01');
        $nextHour = Carbon::parse('2024-01-15 16:00:00');
        
        $this->assertCount(0, $todos->readyToBeCreatedAt($beforeHour));
        $this->assertCount(1, $todos->readyToBeCreatedAt($exactHour));
        $this->assertCount(1, $todos->readyToBeCreatedAt($afterHour));
        $this->assertCount(0, $todos->readyToBeCreatedAt($nextHour));
    }

    public function test_complex_boundary_scenario()
    {
        $todos = new Todos();
        
        // Complex scenario: Daily task that crosses day boundary
        $todos->define(Definition::define()
            ->withName('Day Crossing Complex')
            ->create(BeforeDueBy::oneHour())
            ->due(Schedule::create()->daily()->at('00:30')) // Early morning
            ->automaticallyDelete(AfterDueBy::oneHour()));
        
        // Should create at 23:30 previous day
        $createTime = Carbon::parse('2024-01-14 23:30:00');
        $this->assertCount(1, $todos->readyToBeCreatedAt($createTime));
        
        $todoInstance = $todos->nextInstances($createTime)[0];
        
        // Verify times cross day boundary correctly
        $this->assertEquals(
            Carbon::parse('2024-01-14 23:30:00'),
            $todoInstance->createAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-15 00:30:00'),
            $todoInstance->dueAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-15 01:30:00'),
            $todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt()
        );
        
        // Verify all times are exactly 1 hour apart
        $createCarbon = Carbon::instance($todoInstance->createAt());
        $dueCarbon = Carbon::instance($todoInstance->dueAt());
        $deleteCarbon = Carbon::instance($todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt());
        
        $this->assertEquals(3600, $createCarbon->diffInSeconds($dueCarbon));
        $this->assertEquals(3600, $dueCarbon->diffInSeconds($deleteCarbon));
    }

    public function test_scheduling_precision_limits()
    {
        $todos = new Todos();
        
        // Test with second-level precision
        $todos->define(Definition::define()
            ->withName('Precision Test')
            ->due(Schedule::create()->daily()->at('12:34')));
        
        // Test various sub-minute times
        $testTimes = [
            '12:34:00',
            '12:34:15',
            '12:34:30',
            '12:34:45',
            '12:34:59'
        ];
        
        foreach ($testTimes as $time) {
            $testTime = Carbon::parse("2024-01-15 {$time}");
            $ready = $todos->readyToBeCreatedAt($testTime);
            $this->assertCount(1, $ready, "Failed at time {$time}");
        }
        
        // Should not be ready in previous or next minute
        $prevMinute = Carbon::parse('2024-01-15 12:33:59');
        $nextMinute = Carbon::parse('2024-01-15 12:35:00');
        
        $this->assertCount(0, $todos->readyToBeCreatedAt($prevMinute));
        $this->assertCount(0, $todos->readyToBeCreatedAt($nextMinute));
    }
}