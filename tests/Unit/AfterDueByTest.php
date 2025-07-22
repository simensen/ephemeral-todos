<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\Time;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;
use Simensen\EphemeralTodos\Tests\Testing\RelativeTimeDataProvider;

class AfterDueByTest extends TestCase
{
    use RelativeTimeDataProvider;
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

    /**
     * @dataProvider relativeTimeMethodsProvider
     */
    public function testRelativeTimeConvenienceMethods(string $methodName, int $expectedSeconds)
    {
        $method = [AfterDueBy::class, $methodName];
        $instance = call_user_func($method);
        $this->assertEquals($expectedSeconds, $instance->timeInSeconds());
    }

    public function testChainingCompletionAwarenessWithTimeMethods()
    {
        $afterDueBy = AfterDueBy::oneDay()->andIsComplete();

        $this->assertEquals(86400, $afterDueBy->timeInSeconds());
        $this->assertTrue($afterDueBy->appliesWhenComplete());
        $this->assertFalse($afterDueBy->appliesWhenIncomplete());
    }

    // Note: testDifferentTimeDurations is now covered by testRelativeTimeConvenienceMethods data provider

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
