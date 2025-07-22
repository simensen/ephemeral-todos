<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\BeforeDueBy;

/**
 * This test demonstrates the reduced verbosity achieved with TestScenarioBuilder
 * compared to manual Definition creation.
 */
class BuilderDemoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2025-01-19 12:00:00 UTC');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function testOldVerboseApproach()
    {
        // OLD APPROACH: Manual definition creation (15+ lines)
        $definition = Definition::define()
            ->withName('Daily Standup')
            ->withHighPriority()
            ->due(Schedule::create()->dailyAt('09:30'))
            ->create(BeforeDueBy::fifteenMinutes());

        $finalizedDefinition = $definition->finalize();
        $todo = $finalizedDefinition->currentInstance(Carbon::parse('2025-01-19 09:15:00'));

        $this->assertEquals('Daily Standup', $todo->name());
        $this->assertEquals(4, $todo->priority()); // high priority
        $this->assertNotNull($todo->createAt());
        $this->assertNotNull($todo->dueAt());
    }

    public function testNewBuilderApproach()
    {
        // NEW APPROACH: Builder pattern (3-5 lines)
        $scenario = TestScenarioBuilder::dailyMeeting()
            ->withName('Daily Standup');

        $definition = $scenario->buildDefinition();
        $finalizedDefinition = $definition->finalize();
        
        // Test at 9:00 AM when the meeting should be due
        $testTime = Carbon::parse('2025-01-19 09:00:00');
        $todo = $finalizedDefinition->currentInstance($testTime);

        $this->assertEquals('Daily Standup', $todo->name());
        $this->assertEquals(4, $todo->priority()); // high priority from preset
        $this->assertNotNull($todo->dueAt());
        $this->assertEquals($testTime, $todo->dueAt());
    }

    public function testPresetCustomization()
    {
        // CUSTOMIZED PRESET: Start with template, modify as needed
        $scenario = TestScenarioBuilder::weeklyReview()
            ->withName('Sprint Retrospective')
            ->withPriority('high');

        $this->assertEquals('Sprint Retrospective', $scenario->getName());
        $this->assertEquals('high', $scenario->getPriority());
        $this->assertEquals('weekly', $scenario->getScheduleType());
    }

    public function testFluentChaining()
    {
        // FLUENT CHAINING: Complex scenarios with readable method chains
        $scenario = TestScenarioBuilder::create()
            ->withName('Project Deadline')
            ->withPriority('high')
            ->daily()
            ->at('16:00')
            ->createHoursBefore(4)
            ->withTimezone('America/New_York');

        $this->assertEquals('Project Deadline', $scenario->getName());
        $this->assertEquals('high', $scenario->getPriority());
        $this->assertEquals('daily', $scenario->getScheduleType());
        $this->assertEquals('16:00', $scenario->getScheduleTime());
        $this->assertEquals('America/New_York', $scenario->getTimezone());
    }
}