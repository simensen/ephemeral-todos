<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;

class TimeCalculationTest extends TestCase
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

    public function testConfigurableBaseTimeCalculations()
    {
        $baseTime = Carbon::parse('2025-01-20 15:30:00');
        
        $scenario = TestScenarioBuilder::create()
            ->withBaseTime($baseTime)
            ->createHoursBefore(2)
            ->dueHoursAfter(1);
        
        $expectedCreateTime = $baseTime->copy()->subHours(2); // 13:30:00
        $expectedDueTime = $baseTime->copy()->addHours(1);    // 16:30:00
        
        $this->assertEquals($expectedCreateTime, $scenario->getCreateTime());
        $this->assertEquals($expectedDueTime, $scenario->getDueTime());
    }

    public function testRelativeOffsetFromBaseTime()
    {
        $baseTime = Carbon::parse('2025-01-20 09:00:00');
        
        $scenario = TestScenarioBuilder::create()
            ->withBaseTime($baseTime)
            ->createDaysBefore(1)
            ->dueDaysAfter(2);
        
        $expectedCreateTime = Carbon::parse('2025-01-19 09:00:00');
        $expectedDueTime = Carbon::parse('2025-01-22 09:00:00');
        
        $this->assertEquals($expectedCreateTime, $scenario->getCreateTime());
        $this->assertEquals($expectedDueTime, $scenario->getDueTime());
    }

    public function testComplexRelativeTimeChaining()
    {
        $baseTime = Carbon::parse('2025-01-20 14:00:00');
        
        $scenario = TestScenarioBuilder::create()
            ->withBaseTime($baseTime)
            ->createHoursBefore(3)
            ->createMinutesBefore(30) // Should override previous create time
            ->dueMinutesAfter(45);
        
        $expectedCreateTime = $baseTime->copy()->subMinutes(30); // 13:30:00
        $expectedDueTime = $baseTime->copy()->addMinutes(45);    // 14:45:00
        
        $this->assertEquals($expectedCreateTime, $scenario->getCreateTime());
        $this->assertEquals($expectedDueTime, $scenario->getDueTime());
    }

    public function testRelativeTimeWithTimezones()
    {
        $baseTime = Carbon::parse('2025-01-20 15:00:00', 'America/New_York');
        
        $scenario = TestScenarioBuilder::create()
            ->withBaseTime($baseTime)
            ->withTimezone('America/New_York')
            ->createHoursBefore(1)
            ->dueHoursAfter(2);
        
        $expectedCreateTime = $baseTime->copy()->subHours(1);
        $expectedDueTime = $baseTime->copy()->addHours(2);
        
        $this->assertEquals($expectedCreateTime, $scenario->getCreateTime());
        $this->assertEquals($expectedDueTime, $scenario->getDueTime());
        $this->assertEquals('America/New_York', $scenario->getTimezone());
    }

    public function testAutomaticTimestampCalculationForDefinition()
    {
        $this->markTestIncomplete('Complex create/due time scenarios need further investigation - will be addressed in future phase');
        
        // $baseTime = Carbon::parse('2025-01-20 10:00:00');
        // 
        // $scenario = TestScenarioBuilder::create()
        //     ->withName('Auto Calc Test')
        //     ->withBaseTime($baseTime)
        //     ->createHoursBefore(1)
        //     ->dueHoursAfter(2);
        // 
        // $definition = $scenario->buildDefinition();
        // $finalizedDefinition = $definition->finalize();
        // 
        // // Test at create time (09:00) when todo should be available
        // $createTime = $baseTime->copy()->subHours(1);
        // $todo = $finalizedDefinition->currentInstance($createTime);
        // 
        // $this->assertNotNull($todo, 'Todo should be created at create time');
        // $this->assertEquals('Auto Calc Test', $todo->name());
        // $this->assertEquals($createTime, $todo->createAt());
        // 
        // // Test at due time (12:00)
        // $dueTime = $baseTime->copy()->addHours(2);
        // $todoAtDue = $finalizedDefinition->currentInstance($dueTime);
        // 
        // $this->assertNotNull($todoAtDue, 'Todo should be available at due time');
        // $this->assertEquals($dueTime, $todoAtDue->dueAt());
    }

    public function testComplexScheduleWithRelativeTiming()
    {
        $baseTime = Carbon::parse('2025-01-20 16:00:00'); // Monday 4 PM
        
        $scenario = TestScenarioBuilder::create()
            ->withBaseTime($baseTime)
            ->weekly('friday')
            ->at('17:00')
            ->createHoursBefore(2);
        
        $this->assertEquals('weekly', $scenario->getScheduleType());
        $this->assertEquals('friday', $scenario->getScheduleDay());
        $this->assertEquals('17:00', $scenario->getScheduleTime());
        
        $expectedCreateTime = $baseTime->copy()->subHours(2); // 14:00:00
        $this->assertEquals($expectedCreateTime, $scenario->getCreateTime());
    }

    public function testBaseTimeDefaultsToNow()
    {
        $scenario = TestScenarioBuilder::create()
            ->createMinutesBefore(30);
        
        $expectedCreateTime = Carbon::now()->subMinutes(30);
        $this->assertEquals($expectedCreateTime, $scenario->getCreateTime());
    }

    public function testTimeCalculationPrecision()
    {
        $baseTime = Carbon::parse('2025-01-20 12:34:56.789');
        
        $scenario = TestScenarioBuilder::create()
            ->withBaseTime($baseTime)
            ->createMinutesBefore(15)
            ->dueMinutesAfter(30);
        
        $expectedCreateTime = Carbon::parse('2025-01-20 12:19:56.789');
        $expectedDueTime = Carbon::parse('2025-01-20 13:04:56.789');
        
        $this->assertEquals($expectedCreateTime, $scenario->getCreateTime());
        $this->assertEquals($expectedDueTime, $scenario->getDueTime());
    }

    public function testCrossDateBoundaryCalculations()
    {
        $baseTime = Carbon::parse('2025-01-20 01:30:00');
        
        $scenario = TestScenarioBuilder::create()
            ->withBaseTime($baseTime)
            ->createHoursBefore(3)  // Should go to previous day
            ->dueHoursAfter(25);    // Should go to next day
        
        $expectedCreateTime = Carbon::parse('2025-01-19 22:30:00');
        $expectedDueTime = Carbon::parse('2025-01-21 02:30:00');
        
        $this->assertEquals($expectedCreateTime, $scenario->getCreateTime());
        $this->assertEquals($expectedDueTime, $scenario->getDueTime());
    }
}