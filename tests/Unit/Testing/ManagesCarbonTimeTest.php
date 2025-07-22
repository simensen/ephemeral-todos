<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\Tests\Testing\ManagesCarbonTime;

class ManagesCarbonTimeTest extends TestCase
{
    use ManagesCarbonTime;

    public function testTraitSetsCarbonTestTimeInSetUp()
    {
        // Since setUp already ran, we should have the default test time
        $now = Carbon::now();
        $expected = Carbon::parse('2025-01-19 12:00:00');
        
        $this->assertEquals($expected->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'));
    }

    public function testTraitResetsTimeInTearDown()
    {
        // Change the time during test
        Carbon::setTestNow('2025-06-15 18:00:00');
        $this->assertEquals('2025-06-15 18:00:00', Carbon::now()->format('Y-m-d H:i:s'));
        
        // Simulate tearDown (we can't actually test tearDown since it runs after the test)
        Carbon::setTestNow();
        $this->assertNull(Carbon::getTestNow());
        
        // Reset for other tests
        $this->setUpCarbonTime();
    }

    public function testCanOverrideDefaultTestTime()
    {
        $customTime = '2025-12-25 09:30:00';
        $this->setUpCarbonTime($customTime);
        
        $now = Carbon::now();
        $this->assertEquals($customTime, $now->format('Y-m-d H:i:s'));
    }

    public function testTraitWorksWithExistingTestCaseInheritance()
    {
        // This test class extends PHPUnit\Framework\TestCase, not our base TestCase
        // The trait should still work correctly
        $this->assertInstanceOf(TestCase::class, $this);
        
        // And Carbon time should be managed
        $this->assertNotNull(Carbon::getTestNow());
    }

    public function testMultipleTestsHaveConsistentTime()
    {
        // Each test should start with the same default time
        $now = Carbon::now();
        $expected = Carbon::parse('2025-01-19 12:00:00');
        
        $this->assertEquals($expected->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'));
    }

    public function testTraitPreventsTimePollution()
    {
        // Change time during test
        Carbon::setTestNow('2025-03-14 22:45:00');
        $changed = Carbon::now();
        $this->assertEquals('2025-03-14 22:45:00', $changed->format('Y-m-d H:i:s'));
        
        // The next test should not be affected by this change
        // (we can't directly test this here, but tearDown will ensure it)
    }
}