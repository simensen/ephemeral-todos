<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\Time;

class AfterDueByTest extends TestCase
{
    public function testCanCreateAfterDueByInstance()
    {
        $afterDueBy = AfterDueBy::oneDay();

        $this->assertInstanceOf(AfterDueBy::class, $afterDueBy);
        $this->assertEquals(86400, $afterDueBy->timeInSeconds());
    }

    public function testHasCompletionAwareMethods()
    {
        $afterDueBy = AfterDueBy::oneDay();

        $this->assertTrue($afterDueBy->appliesWhenComplete());
        $this->assertTrue($afterDueBy->appliesWhenIncomplete());
        $this->assertTrue($afterDueBy->appliesAlways());
    }

    public function testCanConfigureCompletionAwareness()
    {
        $afterDueBy = AfterDueBy::oneDay();

        $completeOnly = $afterDueBy->andIsComplete();
        $this->assertTrue($completeOnly->appliesWhenComplete());
        $this->assertFalse($completeOnly->appliesWhenIncomplete());
        $this->assertFalse($completeOnly->appliesAlways());

        $incompleteOnly = $afterDueBy->andIsIncomplete();
        $this->assertFalse($incompleteOnly->appliesWhenComplete());
        $this->assertTrue($incompleteOnly->appliesWhenIncomplete());
        $this->assertFalse($incompleteOnly->appliesAlways());

        $whetherCompletedOrNot = $afterDueBy->whetherCompletedOrNot();
        $this->assertTrue($whetherCompletedOrNot->appliesWhenComplete());
        $this->assertTrue($whetherCompletedOrNot->appliesWhenIncomplete());
        $this->assertTrue($whetherCompletedOrNot->appliesAlways());
    }

    public function testCompletionAwareMethodsReturnNewInstances()
    {
        $original = AfterDueBy::oneDay();
        $complete = $original->andIsComplete();
        $incomplete = $original->andIsIncomplete();
        $whetherCompletedOrNot = $original->whetherCompletedOrNot();

        $this->assertNotSame($original, $complete);
        $this->assertNotSame($original, $incomplete);
        $this->assertNotSame($original, $whetherCompletedOrNot);
        $this->assertNotSame($complete, $incomplete);
    }

    public function testCanConvertToTimeObject()
    {
        $afterDueBy = AfterDueBy::twoHours();
        $time = $afterDueBy->toTime();

        $this->assertInstanceOf(Time::class, $time);
        $this->assertEquals(7200, $time->inSeconds());
    }

    public function testRelativeTimeConvenienceMethods()
    {
        $this->assertEquals(60, AfterDueBy::oneMinute()->timeInSeconds());
        $this->assertEquals(120, AfterDueBy::twoMinutes()->timeInSeconds());
        $this->assertEquals(300, AfterDueBy::fiveMinutes()->timeInSeconds());
        $this->assertEquals(600, AfterDueBy::tenMinutes()->timeInSeconds());
        $this->assertEquals(900, AfterDueBy::fifteenMinutes()->timeInSeconds());
        $this->assertEquals(1800, AfterDueBy::thirtyMinutes()->timeInSeconds());
        $this->assertEquals(3600, AfterDueBy::oneHour()->timeInSeconds());
        $this->assertEquals(3600, AfterDueBy::sixtyMinutes()->timeInSeconds());
        $this->assertEquals(7200, AfterDueBy::twoHours()->timeInSeconds());
        $this->assertEquals(86400, AfterDueBy::oneDay()->timeInSeconds());
        $this->assertEquals(604800, AfterDueBy::oneWeek()->timeInSeconds());
        $this->assertEquals(604800, AfterDueBy::sevenDays()->timeInSeconds());
    }

    public function testChainingCompletionAwarenessWithTimeMethods()
    {
        $afterDueBy = AfterDueBy::oneDay()->andIsComplete();

        $this->assertEquals(86400, $afterDueBy->timeInSeconds());
        $this->assertTrue($afterDueBy->appliesWhenComplete());
        $this->assertFalse($afterDueBy->appliesWhenIncomplete());
    }

    public function testDifferentTimeDurations()
    {
        $this->assertEquals(1200, AfterDueBy::twentyMinutes()->timeInSeconds());
        $this->assertEquals(2700, AfterDueBy::fortyFiveMinutes()->timeInSeconds());
        $this->assertEquals(5400, AfterDueBy::ninetyMinutes()->timeInSeconds());
        $this->assertEquals(10800, AfterDueBy::threeHours()->timeInSeconds());
        $this->assertEquals(14400, AfterDueBy::fourHours()->timeInSeconds());
        $this->assertEquals(21600, AfterDueBy::sixHours()->timeInSeconds());
        $this->assertEquals(43200, AfterDueBy::twelveHours()->timeInSeconds());
        $this->assertEquals(172800, AfterDueBy::twoDays()->timeInSeconds());
        $this->assertEquals(259200, AfterDueBy::threeDays()->timeInSeconds());
        $this->assertEquals(1209600, AfterDueBy::twoWeeks()->timeInSeconds());
    }
}
