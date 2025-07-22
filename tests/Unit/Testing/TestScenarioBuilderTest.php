<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;
use Simensen\EphemeralTodos\Definition;

class TestScenarioBuilderTest extends TestCase
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

    public function testCanCreateBuilderInstance()
    {
        $builder = TestScenarioBuilder::create();
        
        $this->assertInstanceOf(TestScenarioBuilder::class, $builder);
    }

    public function testBuilderImplementsFluentInterface()
    {
        $builder = TestScenarioBuilder::create();
        
        $result = $builder->withName('Test Todo');
        
        $this->assertInstanceOf(TestScenarioBuilder::class, $result);
        $this->assertNotSame($builder, $result, 'Builder should return new instance for immutability');
    }

    public function testBuilderMaintainsImmutability()
    {
        $original = TestScenarioBuilder::create()->withName('Original');
        $modified = $original->withName('Modified');
        
        $this->assertNotSame($original, $modified);
        $this->assertEquals('Original', $original->getName());
        $this->assertEquals('Modified', $modified->getName());
    }

    public function testCanConfigureBasicProperties()
    {
        $builder = TestScenarioBuilder::create()
            ->withName('Test Todo')
            ->withPriority('high');
        
        $this->assertEquals('Test Todo', $builder->getName());
        $this->assertEquals('high', $builder->getPriority());
    }

    public function testCanSetBaseTime()
    {
        $baseTime = Carbon::parse('2025-01-20 15:30:00');
        $builder = TestScenarioBuilder::create()->withBaseTime($baseTime);
        
        $this->assertEquals($baseTime, $builder->getBaseTime());
    }

    public function testBaseTimeDefaultsToCurrentTime()
    {
        $builder = TestScenarioBuilder::create();
        
        $this->assertEquals(Carbon::now(), $builder->getBaseTime());
    }

    public function testCanCalculateRelativeCreateTime()
    {
        $baseTime = Carbon::parse('2025-01-20 15:30:00');
        $builder = TestScenarioBuilder::create()
            ->withBaseTime($baseTime)
            ->createMinutesBefore(30);
        
        $expectedCreateTime = $baseTime->copy()->subMinutes(30);
        $this->assertEquals($expectedCreateTime, $builder->getCreateTime());
    }

    public function testCanCalculateRelativeDueTime()
    {
        $baseTime = Carbon::parse('2025-01-20 15:30:00');
        $builder = TestScenarioBuilder::create()
            ->withBaseTime($baseTime)
            ->dueHoursAfter(2);
        
        $expectedDueTime = $baseTime->copy()->addHours(2);
        $this->assertEquals($expectedDueTime, $builder->getDueTime());
    }

    public function testCanChainMultipleFluentCalls()
    {
        $baseTime = Carbon::parse('2025-01-20 15:30:00');
        
        $builder = TestScenarioBuilder::create()
            ->withName('Chained Todo')
            ->withPriority('medium')
            ->withBaseTime($baseTime)
            ->createMinutesBefore(15)
            ->dueHoursAfter(1);
        
        $this->assertEquals('Chained Todo', $builder->getName());
        $this->assertEquals('medium', $builder->getPriority());
        $this->assertEquals($baseTime, $builder->getBaseTime());
        $this->assertEquals($baseTime->copy()->subMinutes(15), $builder->getCreateTime());
        $this->assertEquals($baseTime->copy()->addHours(1), $builder->getDueTime());
    }

    public function testBuilderCanCreateDefinition()
    {
        $builder = TestScenarioBuilder::create()
            ->withName('Test Definition');
        
        $definition = $builder->buildDefinition();
        
        $this->assertInstanceOf(Definition::class, $definition);
    }

    public function testBuilderPreservesStateAcrossClones()
    {
        $original = TestScenarioBuilder::create()
            ->withName('Original')
            ->withPriority('high');
        
        $cloned = clone $original;
        
        $this->assertEquals($original->getName(), $cloned->getName());
        $this->assertEquals($original->getPriority(), $cloned->getPriority());
        $this->assertNotSame($original, $cloned);
    }

    public function testCanCreateDailyMeetingPreset()
    {
        $scenario = TestScenarioBuilder::dailyMeeting();
        
        $this->assertInstanceOf(TestScenarioBuilder::class, $scenario);
        $this->assertEquals('Daily Meeting', $scenario->getName());
        $this->assertEquals('high', $scenario->getPriority());
    }

    public function testCanCreateWeeklyReviewPreset()
    {
        $scenario = TestScenarioBuilder::weeklyReview();
        
        $this->assertInstanceOf(TestScenarioBuilder::class, $scenario);
        $this->assertEquals('Weekly Review', $scenario->getName());
        $this->assertEquals('medium', $scenario->getPriority());
    }

    public function testCanCreateQuickReminderPreset()
    {
        $scenario = TestScenarioBuilder::quickReminder();
        
        $this->assertInstanceOf(TestScenarioBuilder::class, $scenario);
        $this->assertEquals('Quick Reminder', $scenario->getName());
        $this->assertEquals('low', $scenario->getPriority());
    }

    public function testCanCustomizePresetTemplates()
    {
        $scenario = TestScenarioBuilder::dailyMeeting()
            ->withName('Team Standup')
            ->withPriority('medium');
        
        $this->assertEquals('Team Standup', $scenario->getName());
        $this->assertEquals('medium', $scenario->getPriority());
    }

    public function testPresetTemplatesAreImmutable()
    {
        $original = TestScenarioBuilder::dailyMeeting();
        $modified = $original->withName('Modified Meeting');
        
        $this->assertNotSame($original, $modified);
        $this->assertEquals('Daily Meeting', $original->getName());
        $this->assertEquals('Modified Meeting', $modified->getName());
    }

    public function testCanCreateSimpleReminderWithTimezone()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Timezone Test')
            ->withTimezone('America/New_York');
        
        $this->assertEquals('America/New_York', $scenario->getTimezone());
    }

    public function testCanSetDailySchedule()
    {
        $scenario = TestScenarioBuilder::create()
            ->daily()
            ->at('09:30');
        
        $this->assertEquals('daily', $scenario->getScheduleType());
        $this->assertEquals('09:30', $scenario->getScheduleTime());
    }

    public function testCanSetWeeklySchedule()
    {
        $scenario = TestScenarioBuilder::create()
            ->weekly('monday')
            ->at('14:00');
        
        $this->assertEquals('weekly', $scenario->getScheduleType());
        $this->assertEquals('monday', $scenario->getScheduleDay());
        $this->assertEquals('14:00', $scenario->getScheduleTime());
    }
}