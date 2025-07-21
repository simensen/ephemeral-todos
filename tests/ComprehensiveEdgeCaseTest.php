<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests;

use Carbon\Carbon;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\BeforeDueBy;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\In;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Time;
use Simensen\EphemeralTodos\Todo;
use Simensen\EphemeralTodos\Todos;
use Simensen\EphemeralTodos\Utils;

class ComprehensiveEdgeCaseTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow('2024-01-15 10:00:00 UTC');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function testExtremeSchedulingFrequency()
    {
        $todos = new Todos();

        // Task that runs every minute
        $todos->define(Definition::define()
            ->withName('Every Minute Task')
            ->due(Schedule::create()->withCronExpression('* * * * *'))); // Every minute

        // Should be ready every minute
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 10:00:00')));
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 10:01:00')));
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 10:02:00')));

        // Should be ready even between minutes (cron * * * * * means every minute)
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 10:00:30')));
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 10:01:59')));
    }

    public function testVeryRareScheduling()
    {
        $todos = new Todos();

        // Task that only runs on February 29th at 2:29 AM (leap years only)
        $todos->define(Definition::define()
            ->withName('Rare Leap Day Task')
            ->due(Schedule::create()->withCronExpression('29 2 29 2 *')));

        // Should be ready on Feb 29, 2024 (leap year)
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-02-29 02:29:00')));

        // Should not be ready on Feb 28, 2024
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::parse('2024-02-28 02:29:00')));

        // Should not be ready on Mar 1, 2024
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::parse('2024-03-01 02:29:00')));

        // Should not be ready on Feb 29, 2025 (non-leap year, date doesn't exist)
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::parse('2025-02-28 02:29:00')));
    }

    public function testComplexMultiConditionScheduling()
    {
        $todos = new Todos();

        // Simpler approach: Test weekdays only first
        $todos->define(Definition::define()
            ->withName('Weekday Only Task')
            ->due(Schedule::create()->withCronExpression('0 14 * * 1-5'))); // 2 PM on weekdays only

        // Monday, Jan 15, 2024 at 2 PM - should be ready (weekday)
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 14:00:00'))); // Monday

        // Saturday, Jan 13, 2024 at 2 PM - should not be ready (weekend)
        $saturday = Carbon::parse('2024-01-13 14:00:00'); // Saturday
        $saturdayReady = $todos->readyToBeCreatedAt($saturday);
        $this->assertCount(0, $saturdayReady, 'Saturday should not match weekday-only schedule');

        // Test a more complex schedule separately
        $todos2 = new Todos();
        $todos2->define(Definition::define()
            ->withName('Business Hours Task')
            ->due(Schedule::create()->withCronExpression('0 9-17 * * 1-5'))); // Business hours weekdays

        // Monday at 8 AM - should not be ready (before business hours)
        $this->assertCount(0, $todos2->readyToBeCreatedAt(Carbon::parse('2024-01-15 08:00:00')));

        // Monday at 9 AM - should be ready (business hours start)
        $this->assertCount(1, $todos2->readyToBeCreatedAt(Carbon::parse('2024-01-15 09:00:00')));

        // Monday at 5 PM - should be ready (17:00 is included in 9-17 range)
        $this->assertCount(1, $todos2->readyToBeCreatedAt(Carbon::parse('2024-01-15 17:00:00')));

        // Monday at 6 PM - should not be ready (after business hours, 18 > 17)
        $this->assertCount(0, $todos2->readyToBeCreatedAt(Carbon::parse('2024-01-15 18:00:00')));
    }

    public function testOverlappingCreateAndDueTimes()
    {
        $todos = new Todos();

        // Task that creates and is due at the same time
        $todos->define(Definition::define()
            ->withName('Instant Task')
            ->create(BeforeDueBy::zeroSeconds())
            ->due(Schedule::create()->daily()->at('12:00')));

        $testTime = Carbon::parse('2024-01-15 12:00:00');
        $todoInstance = $todos->nextInstances($testTime)[0];

        // Create and due times should be identical
        $this->assertEquals($todoInstance->createAt(), $todoInstance->dueAt());
        $this->assertEquals($testTime, $todoInstance->createAt());
        $this->assertEquals($testTime, $todoInstance->dueAt());
    }

    public function testCascadingDeletionScenarios()
    {
        $todos = new Todos();

        // Task with multiple deletion rules
        $todos->define(Definition::define()
            ->withName('Multi-Delete Task')
            ->due(Schedule::create()->daily()->at('08:00'))
            ->automaticallyDelete(AfterDueBy::oneHour()->andIsComplete())
            ->automaticallyDelete(AfterDueBy::oneDay()->andIsIncomplete())
            ->automaticallyDelete(AfterExistingFor::oneWeek()->whetherCompletedOrNot()));

        $todoInstance = $todos->nextInstances(Carbon::parse('2024-01-15 08:00:00'))[0];

        // Verify all deletion times are set correctly
        $this->assertEquals(
            Carbon::parse('2024-01-15 09:00:00'), // 1 hour after due
            $todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-16 08:00:00'), // 1 day after due
            $todoInstance->automaticallyDeleteWhenIncompleteAndAfterDueAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-22 08:00:00'), // 1 week after created
            $todoInstance->automaticallyDeleteWhenCompleteAndAfterExistingAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-22 08:00:00'), // 1 week after created
            $todoInstance->automaticallyDeleteWhenIncompleteAndAfterExistingAt()
        );
    }

    public function testInternationalTimezoneEdgeCases()
    {
        $timezones = [
            'Pacific/Kiritimati',  // UTC+14 (earliest timezone)
            'Pacific/Honolulu',    // UTC-10 (among latest timezones)
            'Asia/Kathmandu',      // UTC+5:45 (non-hour offset)
            'Australia/Adelaide',  // UTC+9:30 (half-hour offset)
        ];

        $todos = new Todos();
        $todos->define(Definition::define()
            ->withName('International Task')
            ->due(Schedule::create()->daily()->at('12:00')));

        foreach ($timezones as $tz) {
            $timezone = new DateTimeZone($tz);
            $localNoon = Carbon::parse('2024-01-15 12:00:00', $timezone);

            // Each timezone should work consistently
            $readyInTimezone = $todos->readyToBeCreatedAt($localNoon);
            $this->assertCount(1, $readyInTimezone, "Failed for timezone: {$tz}");

            $todoInstance = $readyInTimezone[0]->nextInstance($localNoon);
            $this->assertEquals('International Task', $todoInstance->name());
        }
    }

    public function testContentHashUniquenessEdgeCases()
    {
        $createAt = Carbon::parse('2024-01-15 10:00:00')->toDateTimeImmutable();
        $dueAt = Carbon::parse('2024-01-15 12:00:00')->toDateTimeImmutable();

        // Create todos that are very similar but should have different hashes
        $todo1 = new Todo('Test Task', $createAt, 1, $dueAt, 'Description A');
        $todo2 = new Todo('Test Task', $createAt, 1, $dueAt, 'Description B');
        $todo3 = new Todo('Test Task', $createAt, 2, $dueAt, 'Description A'); // Different priority
        $todo4 = new Todo('Test Task 2', $createAt, 1, $dueAt, 'Description A'); // Different name

        $hash1 = $todo1->contentHash();
        $hash2 = $todo2->contentHash();
        $hash3 = $todo3->contentHash();
        $hash4 = $todo4->contentHash();

        // All hashes should be different
        $this->assertNotEquals($hash1, $hash2, 'Different descriptions should have different hashes');
        $this->assertNotEquals($hash1, $hash3, 'Different priorities should have different hashes');
        $this->assertNotEquals($hash1, $hash4, 'Different names should have different hashes');

        // Same todo should always have same hash
        $this->assertEquals($hash1, $todo1->contentHash());
    }

    public function testSchedulingWithMicroseconds()
    {
        $todos = new Todos();

        $todos->define(Definition::define()
            ->withName('Microsecond Test')
            ->due(Schedule::create()->daily()->at('15:30')));

        // Test with various microsecond values - all should round to the same minute
        $times = [
            '2024-01-15 15:30:00.000000',
            '2024-01-15 15:30:00.123456',
            '2024-01-15 15:30:59.999999',
        ];

        foreach ($times as $timeString) {
            $testTime = Carbon::parse($timeString);
            $this->assertCount(1, $todos->readyToBeCreatedAt($testTime),
                "Failed for time: {$timeString}");
        }

        // Should not be ready in the next minute
        $nextMinute = Carbon::parse('2024-01-15 15:31:00.000000');
        $this->assertCount(0, $todos->readyToBeCreatedAt($nextMinute));
    }

    public function testUtilsEdgeCaseBehaviors()
    {
        // Test equalToTheMinute with edge cases
        $time1 = Carbon::parse('2024-01-15 10:30:00.000');
        $time2 = Carbon::parse('2024-01-15 10:30:59.999');
        $time3 = Carbon::parse('2024-01-15 10:31:00.000');

        $this->assertTrue(Utils::equalToTheMinute($time1, $time2));
        $this->assertFalse(Utils::equalToTheMinute($time2, $time3));

        // Test toCarbon with various formats
        $carbonFromString = Utils::toCarbon('2024-01-15 10:30:00');
        $carbonFromCarbon = Utils::toCarbon(Carbon::parse('2024-01-15 10:30:00'));
        $carbonFromDateTime = Utils::toCarbon(Carbon::parse('2024-01-15 10:30:00')->toDateTime());

        $this->assertInstanceOf(Carbon::class, $carbonFromString);
        $this->assertInstanceOf(Carbon::class, $carbonFromCarbon);
        $this->assertInstanceOf(Carbon::class, $carbonFromDateTime);

        // All should represent the same time
        $this->assertTrue(Utils::equalToTheMinute($carbonFromString, $carbonFromCarbon));
        $this->assertTrue(Utils::equalToTheMinute($carbonFromCarbon, $carbonFromDateTime));
    }

    public function testTimeClassExtremeValues()
    {
        // Test very large time values
        $largeTime = new Time(365 * 24 * 3600); // 1 year in seconds
        $this->assertEquals(365 * 24 * 3600, $largeTime->inSeconds());

        // Test zero time
        $zeroTime = new Time(0);
        $this->assertEquals(0, $zeroTime->inSeconds());

        // Test time inversion
        $oneHour = new Time(3600);
        $negativeOneHour = $oneHour->invert();
        $this->assertEquals(-3600, $negativeOneHour->inSeconds());

        // Double inversion should return to original
        $backToPositive = $negativeOneHour->invert();
        $this->assertEquals(3600, $backToPositive->inSeconds());
    }

    public function testDefinitionMethodChainingEdgeCases()
    {
        // Test very long method chain
        $definition = Definition::define()
            ->withName('Chained Task')
            ->withDescription('Long chain test')
            ->withHighPriority()
            ->create(BeforeDueBy::fifteenMinutes())
            ->due(Schedule::create()->daily()->at('16:00'))
            ->automaticallyDelete(AfterDueBy::oneHour())
            ->automaticallyDelete(AfterExistingFor::oneDay()->andIsIncomplete())
            ->finalize();

        $testTime = Carbon::parse('2024-01-15 15:45:00'); // 15 minutes before 16:00
        $todoInstance = $definition->nextInstance($testTime);

        $this->assertEquals('Chained Task', $todoInstance->name());
        $this->assertEquals('Long chain test', $todoInstance->description());
        $this->assertEquals(4, $todoInstance->priority()); // High priority is 4
        $this->assertEquals(Carbon::parse('2024-01-15 15:45:00'), $todoInstance->createAt());
        $this->assertEquals(Carbon::parse('2024-01-15 16:00:00'), $todoInstance->dueAt());
    }

    public function testTodosCollectionWithManyDefinitions()
    {
        $todos = new Todos();

        // Add many definitions
        for ($i = 1; $i <= 50; ++$i) {
            $todos->define(Definition::define()
                ->withName("Task {$i}")
                ->due(Schedule::create()->daily()->at('10:00')));
        }

        // All should be ready at the same time
        $ready = $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 10:00:00'));
        $this->assertCount(50, $ready);

        // Verify each has unique name
        $names = array_map(fn ($def) => $def->nextInstance(Carbon::now())->name(), $ready);
        $this->assertCount(50, array_unique($names));
    }

    public function testScheduleIsDueWithDifferentDateFormats()
    {
        $schedule = Schedule::create()->daily()->at('14:30');

        // Test with different time representations of the same moment
        $carbon = Carbon::parse('2024-01-15 14:30:00');
        $dateTime = $carbon->toDateTime();
        $dateTimeImmutable = $carbon->toDateTimeImmutable();

        $this->assertTrue($schedule->isDue($carbon));
        $this->assertTrue($schedule->isDue($dateTime));
        $this->assertTrue($schedule->isDue($dateTimeImmutable));
    }

    public function testExtremePriorityValues()
    {
        $createAt = Carbon::parse('2024-01-15 10:00:00')->toDateTimeImmutable();

        // Test with extreme priority values
        $minPriority = new Todo('Min Priority', $createAt, PHP_INT_MIN);
        $maxPriority = new Todo('Max Priority', $createAt, PHP_INT_MAX);

        $this->assertEquals(PHP_INT_MIN, $minPriority->priority());
        $this->assertEquals(PHP_INT_MAX, $maxPriority->priority());

        // Content hashes should be different
        $this->assertNotEquals($minPriority->contentHash(), $maxPriority->contentHash());
    }

    public function testUnicodeAndSpecialCharacters()
    {
        $createAt = Carbon::parse('2024-01-15 10:00:00')->toDateTimeImmutable();

        // Test with Unicode and special characters
        $unicodeTodo = new Todo('📅 Task with emoji 🚀', $createAt, 1, null, 'Description with café and naïve words');

        $this->assertEquals('📅 Task with emoji 🚀', $unicodeTodo->name());
        $this->assertEquals('Description with café and naïve words', $unicodeTodo->description());

        // Hash should work with Unicode
        $hash = $unicodeTodo->contentHash();
        $this->assertIsString($hash);

        $decoded = json_decode(base64_decode($hash), true);
        $this->assertEquals('📅 Task with emoji 🚀', $decoded['name']);
        $this->assertEquals('Description with café and naïve words', $decoded['description']);
    }

    public function testRapidScheduleChanges()
    {
        $todos = new Todos();

        // Test schedule that changes multiple times in a short period
        $todos->define(Definition::define()
            ->withName('Rapid Change Task')
            ->due(Schedule::create()->withCronExpression('*/5 * * * *'))); // Every 5 minutes

        $baseTimes = [
            '10:00:00', '10:05:00', '10:10:00', '10:15:00', '10:20:00',
        ];

        foreach ($baseTimes as $time) {
            $testTime = Carbon::parse("2024-01-15 {$time}");
            $this->assertCount(1, $todos->readyToBeCreatedAt($testTime),
                "Failed at time: {$time}");
        }

        $nonScheduledTimes = [
            '10:01:00', '10:03:00', '10:07:00', '10:12:00', '10:18:00',
        ];

        foreach ($nonScheduledTimes as $time) {
            $testTime = Carbon::parse("2024-01-15 {$time}");
            $this->assertCount(0, $todos->readyToBeCreatedAt($testTime),
                "Incorrectly ready at time: {$time}");
        }
    }

    public function testDefinitionImmutability()
    {
        $originalDefinition = Definition::define()
            ->withName('Original Task')
            ->due(Schedule::create()->daily()->at('12:00'));

        // Creating new definitions should not modify the original
        $modifiedDefinition = $originalDefinition
            ->withDescription('Added description')
            ->withHighPriority();

        // Original should still be unmodified (this tests immutability)
        $originalTodo = $originalDefinition->finalize()->nextInstance(Carbon::now());
        $modifiedTodo = $modifiedDefinition->finalize()->nextInstance(Carbon::now());

        $this->assertEquals('Original Task', $originalTodo->name());
        $this->assertNull($originalTodo->description());
        $this->assertNull($originalTodo->priority());

        $this->assertEquals('Original Task', $modifiedTodo->name());
        $this->assertEquals('Added description', $modifiedTodo->description());
        $this->assertEquals(4, $modifiedTodo->priority()); // High priority is 4
    }
}
