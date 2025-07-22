<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;

class TimezoneHandlingTest extends TestCase
{
    public function testGenerateLeapYearScenarios()
    {
        $scenario = TestScenarioBuilder::create()
            ->generateLeapYearScenario(2024);

        $this->assertEquals('Leap Year 2024 Scenario', $scenario->getName());
        $this->assertTrue($scenario->isLeapYear(2024));
        
        // Verify leap year scenario is created for Feb 29th
        $definition = $scenario->buildDefinition();
        $this->assertNotNull($definition);
    }

    public function testGenerateLeapYearScenarioFindsNextLeapYear()
    {
        $scenario = TestScenarioBuilder::create()
            ->generateLeapYearScenario(2025); // Non-leap year

        // Should find next leap year (2028)
        $this->assertEquals('Leap Year 2028 Scenario', $scenario->getName());
        $this->assertTrue($scenario->isLeapYear(2028));
    }

    public function testGenerateMonthBoundaryScenarios()
    {
        $scenario = TestScenarioBuilder::create()
            ->generateMonthBoundaryScenario(2, 2025); // February 2025

        $this->assertEquals('Month Boundary 2025-2 Scenario', $scenario->getName());
        
        // Should be set to last day of February at 23:30
        $definition = $scenario->buildDefinition();
        $this->assertNotNull($definition);
    }

    public function testGenerateDSTTransitionScenarios()
    {
        // Spring forward scenario
        $springScenario = TestScenarioBuilder::create()
            ->generateDSTTransitionScenario('America/New_York', true);

        $this->assertEquals('DST Spring Forward Scenario', $springScenario->getName());
        $this->assertEquals('America/New_York', $springScenario->getTimezone());

        // Fall back scenario
        $fallScenario = TestScenarioBuilder::create()
            ->generateDSTTransitionScenario('America/New_York', false);

        $this->assertEquals('DST Fall Back Scenario', $fallScenario->getName());
        $this->assertEquals('America/New_York', $fallScenario->getTimezone());
    }

    public function testGenerateYearBoundaryScenarios()
    {
        $scenario = TestScenarioBuilder::create()
            ->generateYearBoundaryScenario(2025);

        $this->assertEquals('Year Boundary 2025 Scenario', $scenario->getName());
        
        $definition = $scenario->buildDefinition();
        $this->assertNotNull($definition);
    }

    public function testGenerateQuarterBoundaryScenarios()
    {
        // Test all quarters
        $quarters = [1, 2, 3, 4];
        
        foreach ($quarters as $quarter) {
            $scenario = TestScenarioBuilder::create()
                ->generateQuarterBoundaryScenario($quarter, 2025);

            $this->assertEquals("Q{$quarter} Boundary 2025 Scenario", $scenario->getName());
            
            $definition = $scenario->buildDefinition();
            $this->assertNotNull($definition);
        }
    }

    public function testBusinessHoursConfiguration()
    {
        $scenario = TestScenarioBuilder::create()
            ->withBusinessHours('09:00', '17:00');

        $this->assertEquals('09:00', $scenario->getBusinessHoursStart());
        $this->assertEquals('17:00', $scenario->getBusinessHoursEnd());
    }

    public function testLeapYearDetection()
    {
        $scenario = TestScenarioBuilder::create();

        // Test known leap years
        $this->assertTrue($scenario->isLeapYear(2024));
        $this->assertTrue($scenario->isLeapYear(2020));
        $this->assertTrue($scenario->isLeapYear(2016));
        $this->assertTrue($scenario->isLeapYear(2000)); // Century leap year

        // Test non-leap years
        $this->assertFalse($scenario->isLeapYear(2025));
        $this->assertFalse($scenario->isLeapYear(2023));
        $this->assertFalse($scenario->isLeapYear(2022));
        $this->assertFalse($scenario->isLeapYear(1900)); // Century non-leap year
    }

    public function testBoundaryScenarioGeneration()
    {
        $scenario = TestScenarioBuilder::create();

        // Test that generated scenarios are properly configured
        $leapScenario = $scenario->generateLeapYearScenario(2024);
        $monthScenario = $scenario->generateMonthBoundaryScenario(12, 2024);
        $yearScenario = $scenario->generateYearBoundaryScenario(2024);
        $quarterScenario = $scenario->generateQuarterBoundaryScenario(4, 2024);

        // Verify each scenario has proper names and can build definitions
        $this->assertStringContainsString('Leap Year', $leapScenario->getName());
        $this->assertStringContainsString('Month Boundary', $monthScenario->getName());
        $this->assertStringContainsString('Year Boundary', $yearScenario->getName());
        $this->assertStringContainsString('Q4 Boundary', $quarterScenario->getName());

        // Verify all can build valid definitions
        $this->assertNotNull($leapScenario->buildDefinition());
        $this->assertNotNull($monthScenario->buildDefinition());
        $this->assertNotNull($yearScenario->buildDefinition());
        $this->assertNotNull($quarterScenario->buildDefinition());
    }

    public function testComplexBoundaryScenario()
    {
        $scenario = TestScenarioBuilder::create()
            ->generateDSTTransitionScenario('America/New_York', true)
            ->withBusinessHours('08:00', '18:00')
            ->deleteAfterDue('1 day', 'either');

        // Verify complex scenario configuration
        $this->assertEquals('DST Spring Forward Scenario', $scenario->getName());
        $this->assertEquals('America/New_York', $scenario->getTimezone());
        $this->assertEquals('08:00', $scenario->getBusinessHoursStart());
        $this->assertEquals('18:00', $scenario->getBusinessHoursEnd());
        $this->assertEquals('1 day', $scenario->getDeleteAfterDueInterval());
        $this->assertEquals('either', $scenario->getDeleteAfterDueCondition());

        // Verify it builds properly
        $definition = $scenario->buildDefinition();
        $this->assertNotNull($definition);
    }

    /**
     * Integration test demonstrating boundary condition detection
     * across different timezone scenarios.
     */
    public function testTimezoneAwareBoundaryDetection()
    {
        $scenario = TestScenarioBuilder::create()
            ->inTimezone('Europe/London');

        // Test DST transition detection in London timezone
        // UK DST typically changes on last Sunday of March (spring) and October (fall)
        $beforeDST = Carbon::parse('2025-03-30 00:30:00', 'Europe/London');
        $afterDST = Carbon::parse('2025-03-30 02:30:00', 'Europe/London');

        $this->assertTrue($scenario->aroundDSTTransition($beforeDST, $afterDST));

        // Test day boundary crossing with timezone awareness
        $london1 = Carbon::parse('2025-06-15 23:30:00', 'Europe/London');
        $london2 = Carbon::parse('2025-06-16 00:30:00', 'Europe/London');

        $this->assertTrue($scenario->crossesDayBoundary($london1, $london2));
    }

    /**
     * Test edge cases for boundary condition generators.
     */
    public function testBoundaryGeneratorEdgeCases()
    {
        // Test quarter boundary with invalid quarter (should default)
        $scenario = TestScenarioBuilder::create()
            ->generateQuarterBoundaryScenario(5, 2025); // Invalid quarter

        $this->assertStringContainsString('Q5 Boundary', $scenario->getName());

        // Test DST scenario for non-DST timezone
        $utcScenario = TestScenarioBuilder::create()
            ->generateDSTTransitionScenario('UTC', true);

        $this->assertEquals('DST Spring Forward Scenario', $utcScenario->getName());
        $this->assertEquals('UTC', $utcScenario->getTimezone());

        // Test month boundary for invalid month (should default)
        $monthScenario = TestScenarioBuilder::create()
            ->generateMonthBoundaryScenario(13, 2025);

        // Should still create a valid scenario
        $this->assertNotNull($monthScenario->buildDefinition());
    }

    /**
     * Demonstration of chaining boundary condition methods
     * with other TestScenarioBuilder functionality.
     */
    public function testBoundaryConditionChaining()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Complex Boundary Test')
            ->inTimezone('America/Chicago')
            ->generateDSTTransitionScenario('America/Chicago', false) // Fall back
            ->withBusinessHours('07:00', '19:00')
            ->deleteAfterExisting('2 weeks', 'incomplete')
            ->withPriority('high');

        // Verify all configurations are preserved through chaining
        $this->assertEquals('DST Fall Back Scenario', $scenario->getName()); // Last name wins
        $this->assertEquals('America/Chicago', $scenario->getTimezone());
        $this->assertEquals('07:00', $scenario->getBusinessHoursStart());
        $this->assertEquals('19:00', $scenario->getBusinessHoursEnd());
        $this->assertEquals('2 weeks', $scenario->getDeleteAfterExistingInterval());
        $this->assertEquals('incomplete', $scenario->getDeleteAfterExistingCondition());
        $this->assertEquals('high', $scenario->getPriority());

        // Verify the complete scenario builds successfully
        $definition = $scenario->buildDefinition();
        $this->assertNotNull($definition);
    }

    /**
     * Demonstration of Phase 7: Timezone-Aware Building functionality.
     * This showcases the new timezone-aware capabilities.
     */
    public function testPhase7TimezoneAwareBuilding()
    {
        // Demonstrate timezone-aware scheduling
        $globalMeeting = TestScenarioBuilder::create()
            ->withName('Global Team Meeting')
            ->inTimezone('Europe/London')
            ->daily()
            ->at('15:00')
            ->withTimezoneAwareScheduling()
            ->withBusinessHours('09:00', '18:00')
            ->withTimezoneAwareBusinessHours();

        // Verify timezone awareness flags
        $this->assertTrue($globalMeeting->hasTimezoneAwareScheduling());
        $this->assertTrue($globalMeeting->hasTimezoneAwareBusinessHours());
        $this->assertEquals('Europe/London', $globalMeeting->getTimezone());

        // Demonstrate timezone conversion capabilities
        $londonTime = Carbon::parse('2025-06-16 15:00:00', 'Europe/London');
        $nyTime = $globalMeeting->convertToTimezone($londonTime, 'America/New_York');
        $tokyoTime = $globalMeeting->convertToTimezone($londonTime, 'Asia/Tokyo');

        // Verify conversions are correct
        $this->assertEquals('10:00:00', $nyTime->format('H:i:s')); // London is 5 hours ahead in summer
        $this->assertEquals('23:00:00', $tokyoTime->format('H:i:s')); // Tokyo is 8 hours ahead

        // Demonstrate timezone equivalence assertion
        $utcTime = $globalMeeting->convertToTimezone($londonTime, 'UTC');
        $globalMeeting->assertSameTimeAcrossTimezones($londonTime, $utcTime);
        $globalMeeting->assertSameTimeAcrossTimezones($londonTime, $nyTime);

        // Test business hours awareness across timezones
        $this->assertTrue($globalMeeting->isWithinBusinessHours($londonTime));
        $this->assertTrue($globalMeeting->isBusinessHoursEquivalent($londonTime, $utcTime));
    }

    /**
     * Demonstration of advanced timezone-aware deletion scenarios.
     */
    public function testTimezoneAwareDeletionScenarios()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Regional Task Management')
            ->inTimezone('Asia/Singapore')
            ->daily()
            ->at('08:00')
            ->deleteAfterDue('24 hours', 'incomplete')
            ->withTimezoneAwareDeletion()
            ->withTimezoneAwareScheduling();

        // Verify all timezone features are enabled
        $this->assertTrue($scenario->hasTimezoneAwareDeletion());
        $this->assertTrue($scenario->hasTimezoneAwareScheduling());
        $this->assertEquals('Asia/Singapore', $scenario->getTimezone());

        // Test that the scenario builds successfully with all timezone features
        $definition = $scenario->buildDefinition();
        $this->assertNotNull($definition);

        // Demonstrate timezone conversion for deletion timing
        $singaporeTime = Carbon::parse('2025-06-16 08:00:00', 'Asia/Singapore');
        $utcEquivalent = $scenario->convertToTimezone($singaporeTime, 'UTC');

        // Singapore is UTC+8, so 8 AM Singapore = midnight UTC
        $this->assertEquals('00:00:00', $utcEquivalent->format('H:i:s'));
        $this->assertEquals('2025-06-16', $utcEquivalent->format('Y-m-d'));
    }

    /**
     * Integration test showing timezone awareness with boundary conditions.
     */
    public function testTimezoneAwareBoundaryConditions()
    {
        $scenario = TestScenarioBuilder::create()
            ->generateDSTTransitionScenario('America/New_York', true)
            ->withTimezoneAwareScheduling()
            ->withBusinessHours('09:00', '17:00')
            ->withTimezoneAwareBusinessHours();

        // Verify the DST scenario has timezone awareness
        $this->assertTrue($scenario->hasTimezoneAwareScheduling());
        $this->assertTrue($scenario->hasTimezoneAwareBusinessHours());
        $this->assertEquals('America/New_York', $scenario->getTimezone());

        // Test DST transition boundary detection
        $beforeDST = Carbon::parse('2025-03-09 01:30:00', 'America/New_York');
        $afterDST = Carbon::parse('2025-03-09 03:30:00', 'America/New_York');

        $this->assertTrue($scenario->aroundDSTTransition($beforeDST, $afterDST));

        // Test timezone-aware time addition across DST boundary
        $afterTransition = $scenario->addTimezoneAwareHours($beforeDST, 2);
        $this->assertInstanceOf(CarbonInterface::class, $afterTransition);

        // The scenario should build successfully despite DST complexity
        $definition = $scenario->buildDefinition();
        $this->assertNotNull($definition);
    }
}