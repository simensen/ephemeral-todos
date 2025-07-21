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
    public function testZeroSeconds()
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

    public function testOneMinute()
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

    public function testTwoMinutes()
    {
        $time = AfterDueBy::twoMinutes();
        $this->assertEquals(120, $time->timeInSeconds());

        $time = BeforeDueBy::twoMinutes();
        $this->assertEquals(120, $time->timeInSeconds());
    }

    public function testFiveMinutes()
    {
        $time = AfterDueBy::fiveMinutes();
        $this->assertEquals(300, $time->timeInSeconds());

        $time = BeforeDueBy::fiveMinutes();
        $this->assertEquals(300, $time->timeInSeconds());
    }

    public function testFifteenMinutes()
    {
        $time = AfterDueBy::fifteenMinutes();
        $this->assertEquals(900, $time->timeInSeconds());

        $time = BeforeDueBy::fifteenMinutes();
        $this->assertEquals(900, $time->timeInSeconds());
    }

    public function testThirtyMinutes()
    {
        $time = AfterDueBy::thirtyMinutes();
        $this->assertEquals(1800, $time->timeInSeconds());

        $time = BeforeDueBy::thirtyMinutes();
        $this->assertEquals(1800, $time->timeInSeconds());
    }

    public function testOneHour()
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

    public function testTwoHours()
    {
        $time = AfterDueBy::twoHours();
        $this->assertEquals(7200, $time->timeInSeconds());

        $time = BeforeDueBy::twoHours();
        $this->assertEquals(7200, $time->timeInSeconds());

        $time = In::twoHours();
        $this->assertEquals(7200, $time->timeInSeconds());
    }

    public function testThreeHours()
    {
        $time = AfterDueBy::threeHours();
        $this->assertEquals(10800, $time->timeInSeconds());

        $time = In::threeHours();
        $this->assertEquals(10800, $time->timeInSeconds());
    }

    public function testSixHours()
    {
        $time = AfterDueBy::sixHours();
        $this->assertEquals(21600, $time->timeInSeconds());

        $time = In::sixHours();
        $this->assertEquals(21600, $time->timeInSeconds());
    }

    public function testTwelveHours()
    {
        $time = AfterDueBy::twelveHours();
        $this->assertEquals(43200, $time->timeInSeconds());

        $time = In::twelveHours();
        $this->assertEquals(43200, $time->timeInSeconds());
    }

    public function testOneDay()
    {
        $time = AfterDueBy::oneDay();
        $this->assertEquals(86400, $time->timeInSeconds());

        $time = AfterExistingFor::oneDay();
        $this->assertEquals(86400, $time->timeInSeconds());

        $time = In::oneDay();
        $this->assertEquals(86400, $time->timeInSeconds());
    }

    public function testTwoDays()
    {
        $time = AfterDueBy::twoDays();
        $this->assertEquals(172800, $time->timeInSeconds());

        $time = AfterExistingFor::twoDays();
        $this->assertEquals(172800, $time->timeInSeconds());
    }

    public function testThreeDays()
    {
        $time = AfterDueBy::threeDays();
        $this->assertEquals(259200, $time->timeInSeconds());

        $time = AfterExistingFor::threeDays();
        $this->assertEquals(259200, $time->timeInSeconds());
    }

    public function testOneWeek()
    {
        $time = AfterDueBy::oneWeek();
        $this->assertEquals(604800, $time->timeInSeconds());

        $time = AfterExistingFor::oneWeek();
        $this->assertEquals(604800, $time->timeInSeconds());

        $time = In::oneWeek();
        $this->assertEquals(604800, $time->timeInSeconds());
    }

    public function testTwoWeeks()
    {
        $time = AfterDueBy::twoWeeks();
        $this->assertEquals(1209600, $time->timeInSeconds());

        $time = AfterExistingFor::twoWeeks();
        $this->assertEquals(1209600, $time->timeInSeconds());
    }

    public function testThreeWeeks()
    {
        $time = AfterDueBy::threeWeeks();
        $this->assertEquals(1814400, $time->timeInSeconds());

        $time = AfterExistingFor::threeWeeks();
        $this->assertEquals(1814400, $time->timeInSeconds());
    }

    // Note: oneMonth() method is not available in all classes, skip this test

    public function testToTimeConversion()
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

    public function testTimeInSecondsMethod()
    {
        $time = AfterExistingFor::oneDay();
        $this->assertEquals(86400, $time->timeInSeconds());

        $time2 = In::twoHours();
        $this->assertEquals(7200, $time2->timeInSeconds());
    }

    public function testAllClassesImplementTraitConsistently()
    {
        // Test that all time-related classes provide the same interface
        $classes = [AfterDueBy::class, BeforeDueBy::class, AfterExistingFor::class, In::class];

        foreach ($classes as $class) {
            $instance = $class::oneHour();
            $this->assertEquals(3600, $instance->timeInSeconds());
            $this->assertInstanceOf(Time::class, $instance->toTime());
        }
    }

    public function testEdgeCaseTimeValues()
    {
        // Test some edge cases
        $zeroTime = AfterDueBy::zeroSeconds();
        $this->assertEquals(0, $zeroTime->timeInSeconds());
        $this->assertEquals(0, $zeroTime->toTime()->inSeconds());

        $largeTime = AfterExistingFor::threeWeeks();
        $this->assertEquals(1814400, $largeTime->timeInSeconds());
        $this->assertEquals(1814400, $largeTime->toTime()->inSeconds());
    }

    public function testDifferentClassesReturnSameValues()
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
