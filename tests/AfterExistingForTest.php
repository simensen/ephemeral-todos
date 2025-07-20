<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\Time;

class AfterExistingForTest extends TestCase
{
    public function test_can_create_after_existing_for_instance()
    {
        $afterExistingFor = AfterExistingFor::oneWeek();
        
        $this->assertInstanceOf(AfterExistingFor::class, $afterExistingFor);
        $this->assertEquals(604800, $afterExistingFor->timeInSeconds());
    }

    public function test_has_completion_aware_methods()
    {
        $afterExistingFor = AfterExistingFor::oneDay();
        
        $this->assertTrue($afterExistingFor->appliesWhenComplete());
        $this->assertTrue($afterExistingFor->appliesWhenIncomplete());
        $this->assertTrue($afterExistingFor->appliesAlways());
    }

    public function test_can_configure_completion_awareness()
    {
        $afterExistingFor = AfterExistingFor::oneDay();
        
        $completeOnly = $afterExistingFor->andIsComplete();
        $this->assertTrue($completeOnly->appliesWhenComplete());
        $this->assertFalse($completeOnly->appliesWhenIncomplete());
        $this->assertFalse($completeOnly->appliesAlways());
        
        $incompleteOnly = $afterExistingFor->andIsIncomplete();
        $this->assertFalse($incompleteOnly->appliesWhenComplete());
        $this->assertTrue($incompleteOnly->appliesWhenIncomplete());
        $this->assertFalse($incompleteOnly->appliesAlways());
        
        $whetherCompletedOrNot = $afterExistingFor->whetherCompletedOrNot();
        $this->assertTrue($whetherCompletedOrNot->appliesWhenComplete());
        $this->assertTrue($whetherCompletedOrNot->appliesWhenIncomplete());
        $this->assertTrue($whetherCompletedOrNot->appliesAlways());
    }

    public function test_completion_aware_methods_return_new_instances()
    {
        $original = AfterExistingFor::oneDay();
        $complete = $original->andIsComplete();
        $incomplete = $original->andIsIncomplete();
        $whetherCompletedOrNot = $original->whetherCompletedOrNot();
        
        $this->assertNotSame($original, $complete);
        $this->assertNotSame($original, $incomplete);
        $this->assertNotSame($original, $whetherCompletedOrNot);
        $this->assertNotSame($complete, $incomplete);
    }

    public function test_can_convert_to_time_object()
    {
        $afterExistingFor = AfterExistingFor::fourHours();
        $time = $afterExistingFor->toTime();
        
        $this->assertInstanceOf(Time::class, $time);
        $this->assertEquals(14400, $time->inSeconds());
    }

    public function test_relative_time_convenience_methods()
    {
        $this->assertEquals(60, AfterExistingFor::oneMinute()->timeInSeconds());
        $this->assertEquals(120, AfterExistingFor::twoMinutes()->timeInSeconds());
        $this->assertEquals(300, AfterExistingFor::fiveMinutes()->timeInSeconds());
        $this->assertEquals(600, AfterExistingFor::tenMinutes()->timeInSeconds());
        $this->assertEquals(900, AfterExistingFor::fifteenMinutes()->timeInSeconds());
        $this->assertEquals(1800, AfterExistingFor::thirtyMinutes()->timeInSeconds());
        $this->assertEquals(3600, AfterExistingFor::oneHour()->timeInSeconds());
        $this->assertEquals(3600, AfterExistingFor::sixtyMinutes()->timeInSeconds());
        $this->assertEquals(7200, AfterExistingFor::twoHours()->timeInSeconds());
        $this->assertEquals(86400, AfterExistingFor::oneDay()->timeInSeconds());
        $this->assertEquals(604800, AfterExistingFor::oneWeek()->timeInSeconds());
        $this->assertEquals(604800, AfterExistingFor::sevenDays()->timeInSeconds());
    }

    public function test_chaining_completion_awareness_with_time_methods()
    {
        $afterExistingFor = AfterExistingFor::oneWeek()->andIsIncomplete();
        
        $this->assertEquals(604800, $afterExistingFor->timeInSeconds());
        $this->assertFalse($afterExistingFor->appliesWhenComplete());
        $this->assertTrue($afterExistingFor->appliesWhenIncomplete());
    }

    public function test_different_time_durations()
    {
        $this->assertEquals(1200, AfterExistingFor::twentyMinutes()->timeInSeconds());
        $this->assertEquals(2700, AfterExistingFor::fortyFiveMinutes()->timeInSeconds());
        $this->assertEquals(5400, AfterExistingFor::ninetyMinutes()->timeInSeconds());
        $this->assertEquals(10800, AfterExistingFor::threeHours()->timeInSeconds());
        $this->assertEquals(14400, AfterExistingFor::fourHours()->timeInSeconds());
        $this->assertEquals(21600, AfterExistingFor::sixHours()->timeInSeconds());
        $this->assertEquals(43200, AfterExistingFor::twelveHours()->timeInSeconds());
        $this->assertEquals(172800, AfterExistingFor::twoDays()->timeInSeconds());
        $this->assertEquals(259200, AfterExistingFor::threeDays()->timeInSeconds());
        $this->assertEquals(1209600, AfterExistingFor::twoWeeks()->timeInSeconds());
    }

    public function test_use_case_scenarios()
    {
        // Delete completed todos after 1 day
        $deleteCompleted = AfterExistingFor::oneDay()->andIsComplete();
        $this->assertTrue($deleteCompleted->appliesWhenComplete());
        $this->assertFalse($deleteCompleted->appliesWhenIncomplete());
        
        // Delete incomplete todos after 1 week
        $deleteIncomplete = AfterExistingFor::oneWeek()->andIsIncomplete();
        $this->assertFalse($deleteIncomplete->appliesWhenComplete());
        $this->assertTrue($deleteIncomplete->appliesWhenIncomplete());
        
        // Delete all todos after 2 weeks regardless of completion
        $deleteAll = AfterExistingFor::twoWeeks()->whetherCompletedOrNot();
        $this->assertTrue($deleteAll->appliesWhenComplete());
        $this->assertTrue($deleteAll->appliesWhenIncomplete());
    }
}