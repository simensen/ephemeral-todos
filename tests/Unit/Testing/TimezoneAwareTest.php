<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;

class TimezoneAwareTest extends TestCase
{
    public function testMultiTimezoneScenarioConstruction()
    {
        // Create scenarios in different timezones
        $nyScenario = TestScenarioBuilder::create()
            ->withName('New York Meeting')
            ->inTimezone('America/New_York')
            ->daily()
            ->at('14:00');

        $londonScenario = TestScenarioBuilder::create()
            ->withName('London Meeting')
            ->inTimezone('Europe/London')
            ->daily()
            ->at('19:00'); // Same time as NY in UTC

        $tokyoScenario = TestScenarioBuilder::create()
            ->withName('Tokyo Meeting')
            ->inTimezone('Asia/Tokyo')
            ->daily()
            ->at('03:00'); // Next day in Tokyo time

        // Verify timezones are set correctly
        $this->assertEquals('America/New_York', $nyScenario->getTimezone());
        $this->assertEquals('Europe/London', $londonScenario->getTimezone());
        $this->assertEquals('Asia/Tokyo', $tokyoScenario->getTimezone());

        // All scenarios should build valid definitions
        $this->assertNotNull($nyScenario->buildDefinition());
        $this->assertNotNull($londonScenario->buildDefinition());
        $this->assertNotNull($tokyoScenario->buildDefinition());
    }

    public function testTimezoneAwareTimeConversion()
    {
        $scenario = TestScenarioBuilder::create()
            ->inTimezone('America/Los_Angeles');

        // Test timezone conversion methods
        $utcTime = Carbon::parse('2025-06-15 18:00:00', 'UTC');
        $laTime = $scenario->convertToTimezone($utcTime, 'America/Los_Angeles');
        $nyTime = $scenario->convertToTimezone($utcTime, 'America/New_York');

        $this->assertEquals('11:00:00', $laTime->format('H:i:s')); // UTC-7 in summer
        $this->assertEquals('14:00:00', $nyTime->format('H:i:s')); // UTC-4 in summer

        // Test timezone-aware time comparison
        $this->assertTrue($scenario->isSameTimeAcrossTimezones($utcTime, $laTime));
        $this->assertTrue($scenario->isSameTimeAcrossTimezones($utcTime, $nyTime));
    }

    public function testTimezoneAwareTimeCalculations()
    {
        $scenario = TestScenarioBuilder::create()
            ->inTimezone('Europe/Berlin')
            ->at('2025-06-15 15:00:00');

        // Test timezone-aware time calculations
        $berlinTime = Carbon::parse('2025-06-15 15:00:00', 'Europe/Berlin');
        $utcEquivalent = $scenario->convertToTimezone($berlinTime, 'UTC');

        $this->assertEquals('13:00:00', $utcEquivalent->format('H:i:s')); // Berlin is UTC+2 in summer

        // Test that the scenario maintains timezone context
        $definition = $scenario->buildDefinition();
        $this->assertNotNull($definition);
    }

    public function testTimezoneAwareDSTHandling()
    {
        $scenario = TestScenarioBuilder::create()
            ->inTimezone('America/New_York');

        // Test DST transition handling
        $springForward = Carbon::parse('2025-03-09 01:30:00', 'America/New_York'); // Before DST
        $afterTransition = $scenario->addTimezoneAwareHours($springForward, 2);

        // During spring forward, 2 hours later should skip the missing hour
        $this->assertNotEquals('03:30:00', $afterTransition->format('H:i:s'));

        // Test fall back transition
        $fallBack = Carbon::parse('2025-11-02 01:30:00', 'America/New_York'); // Before DST ends
        $afterFallBack = $scenario->addTimezoneAwareHours($fallBack, 2);

        // Should handle the repeated hour correctly
        $this->assertInstanceOf(CarbonInterface::class, $afterFallBack);
    }

    public function testMultiTimezoneAssertion()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Global Team Meeting')
            ->inTimezone('UTC');

        // Create times in different timezones representing the same moment
        $utcTime = Carbon::parse('2025-06-15 18:00:00', 'UTC');
        $nyTime = Carbon::parse('2025-06-15 14:00:00', 'America/New_York');
        $londonTime = Carbon::parse('2025-06-15 19:00:00', 'Europe/London');
        $tokyoTime = Carbon::parse('2025-06-16 03:00:00', 'Asia/Tokyo');

        // Test multi-timezone equivalence assertion
        $timezones = [
            'UTC' => $utcTime,
            'America/New_York' => $nyTime,
            'Europe/London' => $londonTime,
            'Asia/Tokyo' => $tokyoTime,
        ];

        $scenario->assertTimezoneEquivalence($timezones);

        // This should not throw an exception if times are equivalent
        $this->assertTrue(true); // Test passes if no exception thrown
    }

    public function testTimezoneConversionEdgeCases()
    {
        $scenario = TestScenarioBuilder::create()
            ->inTimezone('Pacific/Auckland'); // New Zealand - far from UTC

        // Test edge cases for timezone conversion
        $utcNewYear = Carbon::parse('2025-01-01 00:00:00', 'UTC');
        $nzNewYear = $scenario->convertToTimezone($utcNewYear, 'Pacific/Auckland');

        // New Zealand should be 13 hours ahead (or 12 depending on DST)
        $this->assertGreaterThanOrEqual(12, $nzNewYear->hour);

        // Test invalid timezone handling
        $invalidResult = $scenario->safeConvertToTimezone($utcNewYear, 'Invalid/Timezone');
        $this->assertNull($invalidResult); // Should return null for invalid timezone
    }

    public function testTimezoneAwareScheduleBuilding()
    {
        // Test building schedules that work across timezones
        $scenario = TestScenarioBuilder::create()
            ->withName('Daily Standup')
            ->inTimezone('Europe/London')
            ->daily()
            ->at('09:00')
            ->withTimezoneAwareScheduling();

        $this->assertEquals('Europe/London', $scenario->getTimezone());
        $this->assertTrue($scenario->hasTimezoneAwareScheduling());

        // Build definition with timezone awareness
        $definition = $scenario->buildDefinition();
        $this->assertNotNull($definition);
    }

    public function testBusinessHoursAcrossTimezones()
    {
        $scenario = TestScenarioBuilder::create()
            ->inTimezone('America/New_York')
            ->withBusinessHours('09:00', '17:00')
            ->withTimezoneAwareBusinessHours();

        // Test business hours in different timezones (use a Monday)
        $nyBusinessStart = Carbon::parse('2025-06-16 09:00:00', 'America/New_York'); // Monday
        $utcEquivalent = $scenario->convertToTimezone($nyBusinessStart, 'UTC');
        
        $this->assertTrue($scenario->isWithinBusinessHours($nyBusinessStart));
        $this->assertTrue($scenario->isBusinessHoursEquivalent($nyBusinessStart, $utcEquivalent));

        // Test outside business hours
        $nyEvening = Carbon::parse('2025-06-16 20:00:00', 'America/New_York'); // Monday evening
        $this->assertFalse($scenario->isWithinBusinessHours($nyEvening));
    }

    public function testTimezoneAwareDeletionRules()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Timezone-Aware Deletion')
            ->inTimezone('Asia/Tokyo')
            ->daily()
            ->at('10:00')
            ->deleteAfterDue('1 day', 'either')
            ->withTimezoneAwareDeletion();

        // Verify timezone-aware deletion configuration
        $this->assertEquals('Asia/Tokyo', $scenario->getTimezone());
        $this->assertEquals('1 day', $scenario->getDeleteAfterDueInterval());
        $this->assertTrue($scenario->hasTimezoneAwareDeletion());

        // Build and verify the definition works
        $definition = $scenario->buildDefinition();
        $this->assertNotNull($definition);
    }

    /**
     * @dataProvider timezoneTestProvider
     */
    public function testTimezoneConversionAccuracy(string $sourceTimezone, string $targetTimezone, string $sourceTime, string $expectedTargetTime)
    {
        $scenario = TestScenarioBuilder::create()
            ->inTimezone($sourceTimezone);

        $source = Carbon::parse($sourceTime, $sourceTimezone);
        $target = $scenario->convertToTimezone($source, $targetTimezone);

        $this->assertEquals(
            $expectedTargetTime,
            $target->format('Y-m-d H:i:s'),
            "Timezone conversion from {$sourceTimezone} to {$targetTimezone} failed"
        );
    }

    public static function timezoneTestProvider(): array
    {
        return [
            // Summer time conversions (June)
            ['UTC', 'America/New_York', '2025-06-15 18:00:00', '2025-06-15 14:00:00'],
            ['UTC', 'Europe/London', '2025-06-15 18:00:00', '2025-06-15 19:00:00'],
            ['UTC', 'Asia/Tokyo', '2025-06-15 18:00:00', '2025-06-16 03:00:00'],
            
            // Cross-timezone conversions
            ['America/New_York', 'Europe/London', '2025-06-15 14:00:00', '2025-06-15 19:00:00'],
            ['Europe/London', 'Asia/Tokyo', '2025-06-15 19:00:00', '2025-06-16 03:00:00'],
            
            // Winter time conversions (December)
            ['UTC', 'America/New_York', '2025-12-15 18:00:00', '2025-12-15 13:00:00'],
            ['UTC', 'Europe/London', '2025-12-15 18:00:00', '2025-12-15 18:00:00'], // No DST in winter
        ];
    }

    public function testComplexTimezoneScenario()
    {
        // Test a complex scenario involving multiple timezone features
        $scenario = TestScenarioBuilder::create()
            ->withName('Global Daily Report')
            ->inTimezone('America/Chicago')
            ->daily()
            ->at('08:00')
            ->withBusinessHours('07:00', '19:00')
            ->deleteAfterDue('2 days', 'incomplete')
            ->withTimezoneAwareScheduling()
            ->withTimezoneAwareBusinessHours()
            ->withTimezoneAwareDeletion();

        // Verify all timezone features are enabled
        $this->assertEquals('America/Chicago', $scenario->getTimezone());
        $this->assertEquals('07:00', $scenario->getBusinessHoursStart());
        $this->assertEquals('19:00', $scenario->getBusinessHoursEnd());
        $this->assertEquals('2 days', $scenario->getDeleteAfterDueInterval());
        $this->assertTrue($scenario->hasTimezoneAwareScheduling());
        $this->assertTrue($scenario->hasTimezoneAwareBusinessHours());
        $this->assertTrue($scenario->hasTimezoneAwareDeletion());

        // Test timezone conversions work correctly
        $chicagoTime = Carbon::parse('2025-06-15 08:00:00', 'America/Chicago');
        $utcTime = $scenario->convertToTimezone($chicagoTime, 'UTC');
        $nyTime = $scenario->convertToTimezone($chicagoTime, 'America/New_York');

        $this->assertEquals('13:00:00', $utcTime->format('H:i:s')); // Chicago is UTC-5 in summer
        $this->assertEquals('09:00:00', $nyTime->format('H:i:s')); // NY is 1 hour ahead of Chicago

        // Verify the scenario builds successfully
        $definition = $scenario->buildDefinition();
        $this->assertNotNull($definition);
    }

    public function testTimezoneAwareAssertionMethods()
    {
        $scenario = TestScenarioBuilder::create()
            ->inTimezone('Europe/Paris');

        // Test timezone-aware assertion methods
        $parisTime = Carbon::parse('2025-06-15 15:00:00', 'Europe/Paris');
        $utcTime = Carbon::parse('2025-06-15 13:00:00', 'UTC');
        $nyTime = Carbon::parse('2025-06-15 09:00:00', 'America/New_York');

        // All represent the same moment in time
        $scenario->assertSameTimeAcrossTimezones($parisTime, $utcTime);
        $scenario->assertSameTimeAcrossTimezones($utcTime, $nyTime);
        $scenario->assertSameTimeAcrossTimezones($parisTime, $nyTime);

        // Test that different times fail the assertion
        $differentTime = Carbon::parse('2025-06-15 16:00:00', 'Europe/Paris');
        
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $scenario->assertSameTimeAcrossTimezones($parisTime, $differentTime);
    }
}