<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\BeforeDueBy;
use Simensen\EphemeralTodos\In;
use Simensen\EphemeralTodos\Time;

class ManagesRelativeTimeTest extends TestCase
{
    public function test_zero_seconds()
    {
        $time = AfterDueBy::zeroSeconds();
        $this->assertEquals(0, $time->timeInSeconds());
        
        $time = BeforeDueBy::zeroSeconds();
        $this->assertEquals(0, $time->timeInSeconds());
        
        $time = AfterExistingFor::zeroSeconds();
        $this->assertEquals(0, $time->timeInSeconds());
        
        $time = In::zeroSeconds();
        $this->assertEquals(0, $time->timeInSeconds());
    }

    public function test_one_minute()
    {
        $time = AfterDueBy::oneMinute();
        $this->assertEquals(60, $time->timeInSeconds());
        
        $time = BeforeDueBy::oneMinute();
        $this->assertEquals(60, $time->timeInSeconds());
        
        $time = AfterExistingFor::oneMinute();
        $this->assertEquals(60, $time->timeInSeconds());
        
        $time = In::oneMinute();
        $this->assertEquals(60, $time->timeInSeconds());
    }

    public function test_two_minutes()
    {
        $time = AfterDueBy::twoMinutes();
        $this->assertEquals(120, $time->timeInSeconds());
        
        $time = BeforeDueBy::twoMinutes();
        $this->assertEquals(120, $time->timeInSeconds());
    }

    public function test_five_minutes()
    {
        $time = AfterDueBy::fiveMinutes();
        $this->assertEquals(300, $time->timeInSeconds());
        
        $time = BeforeDueBy::fiveMinutes();
        $this->assertEquals(300, $time->timeInSeconds());
    }

    public function test_fifteen_minutes()
    {
        $time = AfterDueBy::fifteenMinutes();
        $this->assertEquals(900, $time->timeInSeconds());
        
        $time = BeforeDueBy::fifteenMinutes();
        $this->assertEquals(900, $time->timeInSeconds());
    }

    public function test_thirty_minutes()
    {
        $time = AfterDueBy::thirtyMinutes();
        $this->assertEquals(1800, $time->timeInSeconds());
        
        $time = BeforeDueBy::thirtyMinutes();
        $this->assertEquals(1800, $time->timeInSeconds());
    }

    public function test_one_hour()
    {
        $time = AfterDueBy::oneHour();
        $this->assertEquals(3600, $time->timeInSeconds());
        
        $time = BeforeDueBy::oneHour();
        $this->assertEquals(3600, $time->timeInSeconds());
        
        $time = AfterExistingFor::oneHour();
        $this->assertEquals(3600, $time->timeInSeconds());
        
        $time = In::oneHour();
        $this->assertEquals(3600, $time->timeInSeconds());
    }

    public function test_two_hours()
    {
        $time = AfterDueBy::twoHours();
        $this->assertEquals(7200, $time->timeInSeconds());
        
        $time = BeforeDueBy::twoHours();
        $this->assertEquals(7200, $time->timeInSeconds());
        
        $time = In::twoHours();
        $this->assertEquals(7200, $time->timeInSeconds());
    }

    public function test_three_hours()
    {
        $time = AfterDueBy::threeHours();
        $this->assertEquals(10800, $time->timeInSeconds());
        
        $time = In::threeHours();
        $this->assertEquals(10800, $time->timeInSeconds());
    }

    public function test_six_hours()
    {
        $time = AfterDueBy::sixHours();
        $this->assertEquals(21600, $time->timeInSeconds());
        
        $time = In::sixHours();
        $this->assertEquals(21600, $time->timeInSeconds());
    }

    public function test_twelve_hours()
    {
        $time = AfterDueBy::twelveHours();
        $this->assertEquals(43200, $time->timeInSeconds());
        
        $time = In::twelveHours();
        $this->assertEquals(43200, $time->timeInSeconds());
    }

    public function test_one_day()
    {
        $time = AfterDueBy::oneDay();
        $this->assertEquals(86400, $time->timeInSeconds());
        
        $time = AfterExistingFor::oneDay();
        $this->assertEquals(86400, $time->timeInSeconds());
        
        $time = In::oneDay();
        $this->assertEquals(86400, $time->timeInSeconds());
    }

    public function test_two_days()
    {
        $time = AfterDueBy::twoDays();
        $this->assertEquals(172800, $time->timeInSeconds());
        
        $time = AfterExistingFor::twoDays();
        $this->assertEquals(172800, $time->timeInSeconds());
    }

    public function test_three_days()
    {
        $time = AfterDueBy::threeDays();
        $this->assertEquals(259200, $time->timeInSeconds());
        
        $time = AfterExistingFor::threeDays();
        $this->assertEquals(259200, $time->timeInSeconds());
    }

    public function test_one_week()
    {
        $time = AfterDueBy::oneWeek();
        $this->assertEquals(604800, $time->timeInSeconds());
        
        $time = AfterExistingFor::oneWeek();
        $this->assertEquals(604800, $time->timeInSeconds());
        
        $time = In::oneWeek();
        $this->assertEquals(604800, $time->timeInSeconds());
    }

    public function test_two_weeks()
    {
        $time = AfterDueBy::twoWeeks();
        $this->assertEquals(1209600, $time->timeInSeconds());
        
        $time = AfterExistingFor::twoWeeks();
        $this->assertEquals(1209600, $time->timeInSeconds());
    }

    public function test_three_weeks()
    {
        $time = AfterDueBy::threeWeeks();
        $this->assertEquals(1814400, $time->timeInSeconds());
        
        $time = AfterExistingFor::threeWeeks();
        $this->assertEquals(1814400, $time->timeInSeconds());
    }

    // Note: oneMonth() method is not available in all classes, skip this test

    public function test_to_time_conversion()
    {
        $afterDueBy = AfterDueBy::oneHour();
        $timeObject = $afterDueBy->toTime();
        
        $this->assertInstanceOf(Time::class, $timeObject);
        $this->assertEquals(3600, $timeObject->inSeconds());
        
        $beforeDueBy = BeforeDueBy::thirtyMinutes();
        $timeObject2 = $beforeDueBy->toTime();
        
        $this->assertInstanceOf(Time::class, $timeObject2);
        $this->assertEquals(1800, $timeObject2->inSeconds());
    }

    public function test_time_in_seconds_method()
    {
        $time = AfterExistingFor::oneDay();
        $this->assertEquals(86400, $time->timeInSeconds());
        
        $time2 = In::twoHours();
        $this->assertEquals(7200, $time2->timeInSeconds());
    }

    public function test_all_classes_implement_trait_consistently()
    {
        // Test that all time-related classes provide the same interface
        $classes = [AfterDueBy::class, BeforeDueBy::class, AfterExistingFor::class, In::class];
        
        foreach ($classes as $class) {
            $instance = $class::oneHour();
            $this->assertEquals(3600, $instance->timeInSeconds());
            $this->assertInstanceOf(Time::class, $instance->toTime());
        }
    }

    public function test_edge_case_time_values()
    {
        // Test some edge cases
        $zeroTime = AfterDueBy::zeroSeconds();
        $this->assertEquals(0, $zeroTime->timeInSeconds());
        $this->assertEquals(0, $zeroTime->toTime()->inSeconds());
        
        $largeTime = AfterExistingFor::threeWeeks();
        $this->assertEquals(1814400, $largeTime->timeInSeconds());
        $this->assertEquals(1814400, $largeTime->toTime()->inSeconds());
    }

    public function test_different_classes_return_same_values()
    {
        // Verify that the same named methods return the same values across classes
        $afterDue = AfterDueBy::oneDay();
        $afterExisting = AfterExistingFor::oneDay();
        $in = In::oneDay();
        
        $this->assertEquals($afterDue->timeInSeconds(), $afterExisting->timeInSeconds());
        $this->assertEquals($afterDue->timeInSeconds(), $in->timeInSeconds());
        $this->assertEquals(86400, $afterDue->timeInSeconds());
    }
}