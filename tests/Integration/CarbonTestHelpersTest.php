<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Integration;

use Carbon\Carbon;
use Simensen\EphemeralTodos\Tests\TestCase;
use Simensen\EphemeralTodos\Tests\Testing\ManagesCarbonTime;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\BeforeDueBy;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Todos;

class CarbonTestHelpersTest extends TestCase
{
    use ManagesCarbonTime;

    protected function setUp(): void
    {
        // Use custom test time to match existing test expectations
        $this->setUpCarbonTime('2024-01-15 10:00:00');
    }

    public function testCarbonTestNowFunctionality()
    {
        // Verify test time is set correctly
        $testTime = Carbon::parse('2024-01-15 10:00:00 UTC');
        $this->assertEquals($testTime, Carbon::now());

        // Verify we can change test time
        Carbon::setTestNow('2024-01-15 15:30:00 UTC');
        $newTestTime = Carbon::parse('2024-01-15 15:30:00 UTC');
        $this->assertEquals($newTestTime, Carbon::now());

        // Reset for other tests
        Carbon::setTestNow('2024-01-15 10:00:00 UTC');
    }

    public function testTimeTravelForScheduleTesting()
    {
        $todos = new Todos();

        // Define a daily task at 14:00
        $todos->define(Definition::define()
            ->withName('Afternoon Task')
            ->due(Schedule::create()->daily()->at('14:00')));

        // Not ready at 10:00
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));

        // Travel to 14:00
        $this->travelTo('2024-01-15 14:00:00');

        // Now it should be ready
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::now()));

        // Travel to 15:00 - no longer ready (past the scheduled time)
        $this->travelTo('2024-01-15 15:00:00');
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));
    }

    public function testMultiDayScheduleProgression()
    {
        $todos = new Todos();

        // Weekly task on Tuesdays
        $todos->define(Definition::define()
            ->withName('Tuesday Task')
            ->due(Schedule::create()->weekly()->tuesdays()->at('09:00')));

        // Start on Monday - not ready
        Carbon::setTestNow('2024-01-15 09:00:00 UTC'); // Monday
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));

        // Move to Tuesday - should be ready
        Carbon::setTestNow('2024-01-16 09:00:00 UTC'); // Tuesday
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::now()));

        // Move to Wednesday - not ready
        Carbon::setTestNow('2024-01-17 09:00:00 UTC'); // Wednesday
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));

        // Next Tuesday - ready again
        Carbon::setTestNow('2024-01-23 09:00:00 UTC'); // Next Tuesday
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::now()));
    }

    public function testDeletionTimingWithTimeManipulation()
    {
        $todos = new Todos();

        $todos->define(Definition::define()
            ->withName('Deletion Test Task')
            ->due(Schedule::create()->daily()->at('12:00'))
            ->automaticallyDelete(AfterDueBy::twoHours()));

        // Set time to due time
        Carbon::setTestNow('2024-01-15 12:00:00 UTC');

        $todoInstance = $todos->nextInstances(Carbon::now())[0];

        // Check deletion time is 2 hours after due
        $deletionTime = $todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt();
        $expectedDeletion = Carbon::parse('2024-01-15 14:00:00 UTC');

        $this->assertEquals($expectedDeletion, $deletionTime);

        // Travel to deletion time and verify
        Carbon::setTestNow('2024-01-15 14:00:00 UTC');
        $this->assertEquals(Carbon::now(), $deletionTime);
    }

    public function testBeforeDueCreationTiming()
    {
        $todos = new Todos();

        // Task that creates 30 minutes before due
        $todos->define(Definition::define()
            ->withName('Early Reminder Task')
            ->create(BeforeDueBy::thirtyMinutes())
            ->due(Schedule::create()->daily()->at('16:00')));

        // Should create at 15:30
        Carbon::setTestNow('2024-01-15 15:30:00 UTC');
        $readyToCreate = $todos->readyToBeCreatedAt(Carbon::now());
        $this->assertCount(1, $readyToCreate);

        $todoInstance = $readyToCreate[0]->nextInstance(Carbon::now());

        // Verify create and due times
        $this->assertEquals(
            Carbon::parse('2024-01-15 15:30:00 UTC'),
            $todoInstance->createAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-15 16:00:00 UTC'),
            $todoInstance->dueAt()
        );
    }

    public function testMonthBoundaryCrossing()
    {
        $todos = new Todos();

        // Monthly task on the 1st
        $todos->define(Definition::define()
            ->withName('Monthly Report')
            ->due(Schedule::create()->monthlyOn(1, '09:00')));

        // Test end of January
        Carbon::setTestNow('2024-01-31 09:00:00 UTC');
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));

        // Test start of February
        Carbon::setTestNow('2024-02-01 09:00:00 UTC');
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::now()));

        // Test mid-February
        Carbon::setTestNow('2024-02-15 09:00:00 UTC');
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));

        // Test start of March
        Carbon::setTestNow('2024-03-01 09:00:00 UTC');
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::now()));
    }

    public function testYearBoundaryCrossing()
    {
        $todos = new Todos();

        // Daily task
        $todos->define(Definition::define()
            ->withName('Daily Year-End Task')
            ->due(Schedule::create()->daily()->at('12:00')));

        // Test New Year's Eve
        Carbon::setTestNow('2024-12-31 12:00:00 UTC');
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::now()));

        // Test New Year's Day
        Carbon::setTestNow('2025-01-01 12:00:00 UTC');
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::now()));

        // Both should work the same way
        $nye2024Instance = $todos->nextInstances(Carbon::parse('2024-12-31 12:00:00 UTC'))[0];
        $nyd2025Instance = $todos->nextInstances(Carbon::parse('2025-01-01 12:00:00 UTC'))[0];

        $this->assertEquals('Daily Year-End Task', $nye2024Instance->name());
        $this->assertEquals('Daily Year-End Task', $nyd2025Instance->name());
    }

    public function testLeapYearHandling()
    {
        $todos = new Todos();

        // Task on February 29th (only exists in leap years)
        $todos->define(Definition::define()
            ->withName('Leap Day Task')
            ->due(Schedule::create()->monthlyOn(29, '12:00')));

        // Test February 29, 2024 (leap year)
        Carbon::setTestNow('2024-02-29 12:00:00 UTC');
        $readyLeapYear = $todos->readyToBeCreatedAt(Carbon::now());
        $this->assertCount(1, $readyLeapYear);

        // Test February 28, 2023 (non-leap year)
        Carbon::setTestNow('2023-02-28 12:00:00 UTC');
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));

        // Test March 1, 2023 (day after Feb 28 in non-leap year)
        Carbon::setTestNow('2023-03-01 12:00:00 UTC');
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));
    }

    public function testRapidTimeProgression()
    {
        $todos = new Todos();

        // Hourly task
        $todos->define(Definition::define()
            ->withName('Hourly Task')
            ->due(Schedule::create()->hourly()));

        $readyTimes = [];

        // Test every 15 minutes for 4 hours
        for ($i = 0; $i < 16; ++$i) {
            $testTime = Carbon::parse('2024-01-15 10:00:00 UTC')->addMinutes($i * 15);
            Carbon::setTestNow($testTime);

            $ready = $todos->readyToBeCreatedAt(Carbon::now());
            if (count($ready) > 0) {
                $readyTimes[] = $testTime->format('H:i');
            }
        }

        // Should be ready at the top of each hour
        $this->assertContains('10:00', $readyTimes);
        $this->assertContains('11:00', $readyTimes);
        $this->assertContains('12:00', $readyTimes);
        $this->assertContains('13:00', $readyTimes);

        // Should not be ready at quarter hours
        $this->assertNotContains('10:15', $readyTimes);
        $this->assertNotContains('10:30', $readyTimes);
        $this->assertNotContains('10:45', $readyTimes);
    }

    public function testTimePrecisionAndRounding()
    {
        $todos = new Todos();

        $todos->define(Definition::define()
            ->withName('Precision Test Task')
            ->due(Schedule::create()->daily()->at('14:30')));

        // Test slightly before 14:30
        Carbon::setTestNow('2024-01-15 14:29:59 UTC');
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));

        // Test exactly at 14:30:00
        Carbon::setTestNow('2024-01-15 14:30:00 UTC');
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::now()));

        // Test with microseconds
        Carbon::setTestNow('2024-01-15 14:30:00.123456 UTC');
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::now()));

        // Test slightly after 14:30
        Carbon::setTestNow('2024-01-15 14:30:59 UTC');
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::now()));

        // Test at 14:31
        Carbon::setTestNow('2024-01-15 14:31:00 UTC');
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));
    }

    public function testTimeIsolationBetweenTests()
    {
        // This test verifies that time changes don't leak between tests

        // Change time within this test
        Carbon::setTestNow('2024-06-15 18:00:00 UTC');
        $changedTime = Carbon::now();

        $this->assertEquals('2024-06-15 18:00:00', $changedTime->format('Y-m-d H:i:s'));

        // Note: tearDown() will reset this, so the next test should start clean
    }

    public function testTimeWasResetFromPreviousTest()
    {
        // This test verifies that tearDown() properly reset the time
        $currentTime = Carbon::now();

        // Should be back to the setUp() time, not the changed time from previous test
        $this->assertEquals('2024-01-15 10:00:00', $currentTime->format('Y-m-d H:i:s'));
    }

    public function testComplexSchedulingScenarioWithTimeTravel()
    {
        $todos = new Todos();

        // Multiple overlapping schedules
        $todos->define(Definition::define()
            ->withName('Daily Morning')
            ->due(Schedule::create()->daily()->at('09:00')));

        $todos->define(Definition::define()
            ->withName('Weekly Monday')
            ->due(Schedule::create()->weekly()->mondays()->at('09:00')));

        $todos->define(Definition::define()
            ->withName('Monthly 15th')
            ->due(Schedule::create()->monthlyOn(15, '09:00')));

        // Test on Monday, January 15th at 9:00 AM - all should be ready
        Carbon::setTestNow('2024-01-15 09:00:00 UTC');
        $allReady = $todos->readyToBeCreatedAt(Carbon::now());
        $this->assertCount(3, $allReady);

        // Test on Tuesday, January 16th at 9:00 AM - only daily should be ready
        Carbon::setTestNow('2024-01-16 09:00:00 UTC');
        $dailyOnly = $todos->readyToBeCreatedAt(Carbon::now());
        $this->assertCount(1, $dailyOnly);

        // Test on Monday, January 22nd at 9:00 AM - daily + weekly should be ready
        Carbon::setTestNow('2024-01-22 09:00:00 UTC');
        $dailyAndWeekly = $todos->readyToBeCreatedAt(Carbon::now());
        $this->assertCount(2, $dailyAndWeekly);
    }
}
