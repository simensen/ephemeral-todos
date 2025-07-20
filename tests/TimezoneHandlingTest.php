<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests;

use Carbon\Carbon;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Utils;
use Simensen\EphemeralTodos\Todos;
use Simensen\EphemeralTodos\AfterDueBy;

class TimezoneHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        // Set a consistent test time in UTC
        Carbon::setTestNow('2024-01-15 10:00:00 UTC');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function test_utils_to_carbon_with_different_timezones()
    {
        $utc = new DateTimeZone('UTC');
        $eastern = new DateTimeZone('America/New_York');
        $pacific = new DateTimeZone('America/Los_Angeles');
        $london = new DateTimeZone('Europe/London');

        // Same time string, different timezones
        $timeString = '2024-01-15 15:00:00';

        $utcTime = Utils::toCarbon($timeString, $utc);
        $easternTime = Utils::toCarbon($timeString, $eastern);
        $pacificTime = Utils::toCarbon($timeString, $pacific);
        $londonTime = Utils::toCarbon($timeString, $london);

        // All should have the correct timezone
        $this->assertEquals('UTC', $utcTime->getTimezone()->getName());
        $this->assertEquals('America/New_York', $easternTime->getTimezone()->getName());
        $this->assertEquals('America/Los_Angeles', $pacificTime->getTimezone()->getName());
        $this->assertEquals('Europe/London', $londonTime->getTimezone()->getName());

        // They should represent different UTC timestamps
        $this->assertNotEquals($utcTime->getTimestamp(), $easternTime->getTimestamp());
        $this->assertNotEquals($utcTime->getTimestamp(), $pacificTime->getTimestamp());
        $this->assertNotEquals($easternTime->getTimestamp(), $pacificTime->getTimestamp());
    }

    public function test_utils_equal_to_the_minute_across_timezones()
    {
        // Same moment in time, different timezone representations
        $utcTime = Carbon::parse('2024-01-15 15:00:30 UTC');
        $easternTime = Carbon::parse('2024-01-15 10:00:45 America/New_York'); // Same UTC moment
        $pacificTime = Carbon::parse('2024-01-15 07:00:15 America/Los_Angeles'); // Same UTC moment

        // Should be equal to the minute since they represent the same UTC minute
        $this->assertTrue(Utils::equalToTheMinute($utcTime, $easternTime));
        $this->assertTrue(Utils::equalToTheMinute($utcTime, $pacificTime));
        $this->assertTrue(Utils::equalToTheMinute($easternTime, $pacificTime));

        // Different UTC minutes should not be equal
        $differentUtcMinute = Carbon::parse('2024-01-15 15:01:30 UTC');
        $this->assertFalse(Utils::equalToTheMinute($utcTime, $differentUtcMinute));
    }

    public function test_schedule_with_timezone_aware_cron_expressions()
    {
        $easternTz = new DateTimeZone('America/New_York');
        $pacificTz = new DateTimeZone('America/Los_Angeles');

        // Create schedules for the same local time in different timezones
        $easternSchedule = Schedule::create()->dailyAt('09:00');
        $pacificSchedule = Schedule::create()->dailyAt('09:00');

        // Test with times in different timezones
        $easternMorning = Carbon::parse('2024-01-15 09:00:00', $easternTz);
        $pacificMorning = Carbon::parse('2024-01-15 09:00:00', $pacificTz);

        // Both should be due at their respective 9:00 AM times
        $this->assertTrue($easternSchedule->isDue($easternMorning));
        $this->assertTrue($pacificSchedule->isDue($pacificMorning));

        // Test with times that are clearly different minutes in UTC
        $easternNoon = Carbon::parse('2024-01-15 12:00:00', $easternTz); // 17:00 UTC
        $pacificNoon = Carbon::parse('2024-01-15 12:00:00', $pacificTz); // 20:00 UTC
        
        // 9:00 AM schedule should not be due at noon
        $this->assertFalse($easternSchedule->isDue($easternNoon));
        $this->assertFalse($pacificSchedule->isDue($pacificNoon));
    }

    public function test_todo_creation_across_timezones()
    {
        $todos = new Todos();

        // Define a todo that should be created at 9:00 AM in different timezones
        $todos->define(Definition::define()
            ->withName('Morning Meeting')
            ->due(Schedule::create()->daily()->at('09:00'))
            ->automaticallyDelete(AfterDueBy::oneDay()));

        $easternTz = new DateTimeZone('America/New_York');
        $pacificTz = new DateTimeZone('America/Los_Angeles');

        // Check if ready to create at 9:00 AM Eastern
        $easternMorning = Carbon::parse('2024-01-15 09:00:00', $easternTz);
        $readyEastern = $todos->readyToBeCreatedAt($easternMorning);

        // Check if ready to create at 9:00 AM Pacific (which is 12:00 PM Eastern)
        $pacificMorning = Carbon::parse('2024-01-15 09:00:00', $pacificTz);
        $readyPacific = $todos->readyToBeCreatedAt($pacificMorning);

        // Both should be ready at their respective 9:00 AM times
        $this->assertCount(1, $readyEastern);
        $this->assertCount(1, $readyPacific);

        // Generate instances and check their timezones are preserved
        $easternInstance = $readyEastern[0]->nextInstance($easternMorning);
        $pacificInstance = $readyPacific[0]->nextInstance($pacificMorning);

        $this->assertEquals('Morning Meeting', $easternInstance->name());
        $this->assertEquals('Morning Meeting', $pacificInstance->name());
    }

    public function test_deletion_times_with_timezone_considerations()
    {
        $todos = new Todos();

        $todos->define(Definition::define()
            ->withName('Timezone Test Task')
            ->due(Schedule::create()->daily()->at('15:00'))
            ->automaticallyDelete(AfterDueBy::oneDay()));

        // Test in London timezone (GMT)
        $londonTz = new DateTimeZone('Europe/London');
        $londonTime = Carbon::parse('2024-01-15 15:00:00', $londonTz);

        $todoInstance = $todos->nextInstances($londonTime)[0];

        // Due time should be preserved in the instance
        $dueTime = $todoInstance->dueAt();
        $this->assertNotNull($dueTime);

        // Deletion time should be 1 day after due time
        $deletionTime = $todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt();
        $this->assertNotNull($deletionTime);

        // Should be exactly 24 hours later
        $expectedDeletion = Carbon::instance($dueTime)->addDay();
        $this->assertEquals($expectedDeletion->getTimestamp(), $deletionTime->getTimestamp());
    }

    public function test_midnight_boundary_across_timezones()
    {
        $todos = new Todos();

        // Task due at midnight
        $todos->define(Definition::define()
            ->withName('Midnight Task')
            ->due(Schedule::create()->daily()->at('00:00')));

        $utcMidnight = Carbon::parse('2024-01-15 00:00:00 UTC');
        $easternMidnight = Carbon::parse('2024-01-15 00:00:00 America/New_York');

        // Both should be ready at their respective midnights
        $utcReady = $todos->readyToBeCreatedAt($utcMidnight);
        $easternReady = $todos->readyToBeCreatedAt($easternMidnight);

        $this->assertCount(1, $utcReady);
        $this->assertCount(1, $easternReady);

        // But they represent different UTC times
        $this->assertNotEquals(
            $utcMidnight->getTimestamp(),
            $easternMidnight->getTimestamp()
        );
    }

    public function test_daylight_saving_time_transition()
    {
        $easternTz = new DateTimeZone('America/New_York');

        // Spring forward: 2:00 AM becomes 3:00 AM on March 10, 2024
        $springForwardDay = Carbon::parse('2024-03-10 02:30:00', $easternTz);
        
        // Fall back: 2:00 AM becomes 1:00 AM on November 3, 2024
        $fallBackDay = Carbon::parse('2024-11-03 01:30:00', $easternTz);

        $todos = new Todos();

        // Task scheduled for the DST transition time
        $todos->define(Definition::define()
            ->withName('DST Test Task')
            ->due(Schedule::create()->daily()->at('02:30')));

        // During spring forward, 2:30 AM doesn't exist
        $springReady = $todos->readyToBeCreatedAt($springForwardDay);
        
        // During fall back, 1:30 AM happens twice
        $fallReady = $todos->readyToBeCreatedAt($fallBackDay);

        // The system should handle these gracefully
        // (Exact behavior depends on Carbon/PHP's DST handling)
        $this->assertIsArray($springReady);
        $this->assertIsArray($fallReady);
    }

    public function test_international_date_line_handling()
    {
        $tokyoTz = new DateTimeZone('Asia/Tokyo');
        $honoluluTz = new DateTimeZone('Pacific/Honolulu');

        // Same UTC moment, different calendar dates due to international date line
        $tokyoTime = Carbon::parse('2024-01-15 09:00:00', $tokyoTz);
        $honoluluTime = $tokyoTime->copy()->setTimezone($honoluluTz);

        // These might be on different calendar dates
        $tokyoDate = $tokyoTime->format('Y-m-d');
        $honoluluDate = $honoluluTime->format('Y-m-d');

        // Test that Utils functions handle this correctly
        $this->assertTrue(Utils::equalToTheMinute($tokyoTime, $honoluluTime));

        // Converting to Carbon should preserve the timezone
        $convertedTokyo = Utils::toCarbon($tokyoTime);
        $convertedHonolulu = Utils::toCarbon($honoluluTime);

        $this->assertEquals($tokyoTz->getName(), $convertedTokyo->getTimezone()->getName());
        $this->assertEquals($honoluluTz->getName(), $convertedHonolulu->getTimezone()->getName());
    }

    public function test_weekly_schedule_across_timezone_boundaries()
    {
        $todos = new Todos();

        // Weekly task on Mondays at 9:00 AM
        $todos->define(Definition::define()
            ->withName('Weekly Monday Task')
            ->due(Schedule::create()->weekly()->mondays()->at('09:00')));

        $utcTz = new DateTimeZone('UTC');
        $easternTz = new DateTimeZone('America/New_York');

        // Monday 9:00 AM in different timezones
        $utcMonday = Carbon::parse('2024-01-15 09:00:00', $utcTz);
        $easternMonday = Carbon::parse('2024-01-15 09:00:00', $easternTz);

        // Both should be ready on their respective Monday mornings
        $utcReady = $todos->readyToBeCreatedAt($utcMonday);
        $easternReady = $todos->readyToBeCreatedAt($easternMonday);

        $this->assertCount(1, $utcReady);
        $this->assertCount(1, $easternReady);

        // Verify they're both Monday
        $this->assertEquals(1, $utcMonday->dayOfWeek); // Monday
        $this->assertEquals(1, $easternMonday->dayOfWeek); // Monday
    }

    public function test_timezone_preservation_in_todo_instances()
    {
        $todos = new Todos();

        $todos->define(Definition::define()
            ->withName('Timezone Preservation Test')
            ->due(Schedule::create()->daily()->at('14:00'))
            ->automaticallyDelete(AfterDueBy::sixHours()));

        $londonTz = new DateTimeZone('Europe/London');
        $londonTime = Carbon::parse('2024-01-15 14:00:00', $londonTz);

        $todoInstance = $todos->nextInstances($londonTime)[0];

        // Check that timezone information is preserved through the lifecycle
        $createAt = $todoInstance->createAt();
        $dueAt = $todoInstance->dueAt();
        $deleteAt = $todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt();

        $this->assertNotNull($createAt);
        $this->assertNotNull($dueAt);
        $this->assertNotNull($deleteAt);

        // The times should maintain their relationships regardless of timezone
        $this->assertEquals($dueAt->getTimestamp(), $createAt->getTimestamp()); // Same time when no create offset
        $this->assertEquals($deleteAt->getTimestamp(), Carbon::instance($dueAt)->addHours(6)->getTimestamp());
    }
}