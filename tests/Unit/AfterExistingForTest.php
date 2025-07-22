<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\Time;
use Simensen\EphemeralTodos\Tests\Testing\RelativeTimeDataProvider;
use Simensen\EphemeralTodos\Tests\Testing\AssertsCompletionAwareness;
use Simensen\EphemeralTodos\Tests\Testing\AssertsImmutability;

class AfterExistingForTest extends TestCase
{
    use RelativeTimeDataProvider, AssertsCompletionAwareness, AssertsImmutability;
    public function testCanCreateAfterExistingForInstance()
    {
        $afterExistingFor = AfterExistingFor::oneWeek();

        $this->assertInstanceOf(AfterExistingFor::class, $afterExistingFor);
        $this->assertEquals(604800, $afterExistingFor->timeInSeconds());
    }

    public function testHasCompletionAwareMethods()
    {
        $afterExistingFor = AfterExistingFor::oneDay();
        $this->assertHasCompletionAwareMethods($afterExistingFor);
    }

    public function testCanConfigureCompletionAwareness()
    {
        $afterExistingFor = AfterExistingFor::oneDay();
        $this->assertCanConfigureCompletionAwareness($afterExistingFor);
    }

    public function testCompletionAwareMethodsReturnNewInstances()
    {
        $original = AfterExistingFor::oneDay();
        $this->assertMultipleMethodsReturnNewInstances($original, [
            'andIsComplete',
            'andIsIncomplete',
            'whetherCompletedOrNot'
        ]);
    }

    public function testCanConvertToTimeObject()
    {
        $afterExistingFor = AfterExistingFor::fourHours();
        $time = $afterExistingFor->toTime();

        $this->assertInstanceOf(Time::class, $time);
        $this->assertEquals(14400, $time->inSeconds());
    }

    /**
     * @dataProvider relativeTimeMethodsProvider
     */
    public function testRelativeTimeConvenienceMethods(string $methodName, int $expectedSeconds)
    {
        $method = [AfterExistingFor::class, $methodName];
        $instance = call_user_func($method);
        $this->assertEquals($expectedSeconds, $instance->timeInSeconds());
    }

    public function testChainingCompletionAwarenessWithTimeMethods()
    {
        $afterExistingFor = AfterExistingFor::oneWeek()->andIsIncomplete();

        $this->assertEquals(604800, $afterExistingFor->timeInSeconds());
        $this->assertFalse($afterExistingFor->appliesWhenComplete());
        $this->assertTrue($afterExistingFor->appliesWhenIncomplete());
    }

    // Note: testDifferentTimeDurations is now covered by testRelativeTimeConvenienceMethods data provider

    public function testUseCaseScenarios()
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
