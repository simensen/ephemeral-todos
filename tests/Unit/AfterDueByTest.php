<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\Time;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;

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

    /**
     * Demonstration of TestScenarioBuilder integration with AfterDueBy deletion rules.
     * This showcases Phase 5: Deletion Rule Management functionality.
     */
    public function testTestScenarioBuilderAfterDueByIntegration()
    {
        // Demonstrate TestScenarioBuilder creating AfterDueBy rules from intervals
        $scenario = TestScenarioBuilder::create()
            ->withName('AfterDueBy Demo')
            ->daily()
            ->at('09:00')
            ->deleteAfterDue('2 hours', 'complete');

        // Verify the interval conversion matches AfterDueBy values
        $this->assertEquals('2 hours', $scenario->getDeleteAfterDueInterval());
        $this->assertEquals('complete', $scenario->getDeleteAfterDueCondition());
        
        // Test that interval conversion aligns with AfterDueBy time values
        $expectedSeconds = AfterDueBy::twoHours()->timeInSeconds();
        $actualSeconds = $scenario->convertIntervalToSeconds('2 hours');
        $this->assertEquals($expectedSeconds, $actualSeconds);

        // Verify different interval mappings
        $testCases = [
            ['interval' => '1 hour', 'afterDueBy' => AfterDueBy::oneHour()],
            ['interval' => '1 day', 'afterDueBy' => AfterDueBy::oneDay()],
            ['interval' => '1 week', 'afterDueBy' => AfterDueBy::oneWeek()],
            ['interval' => '3 days', 'afterDueBy' => AfterDueBy::threeDays()],
        ];

        foreach ($testCases as $testCase) {
            $convertedSeconds = $scenario->convertIntervalToSeconds($testCase['interval']);
            $expectedSeconds = $testCase['afterDueBy']->timeInSeconds();
            
            $this->assertEquals(
                $expectedSeconds,
                $convertedSeconds,
                "Interval '{$testCase['interval']}' should convert to {$expectedSeconds} seconds"
            );
        }
    }

    /**
     * Demonstration of completion state mapping between TestScenarioBuilder and AfterDueBy.
     */
    public function testCompletionStateMappingDemo()
    {
        // Show how TestScenarioBuilder completion states map to AfterDueBy methods
        $baseAfterDueBy = AfterDueBy::oneDay();

        // Default state (either) - should apply to all
        $this->assertTrue($baseAfterDueBy->appliesWhenComplete());
        $this->assertTrue($baseAfterDueBy->appliesWhenIncomplete());
        $this->assertTrue($baseAfterDueBy->appliesAlways());

        // 'complete' state mapping
        $completeOnlyAfterDueBy = $baseAfterDueBy->andIsComplete();
        $this->assertTrue($completeOnlyAfterDueBy->appliesWhenComplete());
        $this->assertFalse($completeOnlyAfterDueBy->appliesWhenIncomplete());
        $this->assertFalse($completeOnlyAfterDueBy->appliesAlways());

        // 'incomplete' state mapping  
        $incompleteOnlyAfterDueBy = $baseAfterDueBy->andIsIncomplete();
        $this->assertFalse($incompleteOnlyAfterDueBy->appliesWhenComplete());
        $this->assertTrue($incompleteOnlyAfterDueBy->appliesWhenIncomplete());
        $this->assertFalse($incompleteOnlyAfterDueBy->appliesAlways());

        // Verify TestScenarioBuilder validates these same states
        $scenario = TestScenarioBuilder::create();
        $this->assertTrue($scenario->isValidCompletionState('complete'));
        $this->assertTrue($scenario->isValidCompletionState('incomplete'));
        $this->assertTrue($scenario->isValidCompletionState('either'));
    }
}
