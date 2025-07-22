<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Integration;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\BeforeDueBy;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Todo;
use Simensen\EphemeralTodos\Todos;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;

class TodoLifecycleIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow('2024-01-15 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function testCompleteDailyTodoLifecycle()
    {
        $todos = new Todos();

        // Define a daily standup meeting
        $todos->define(Definition::define()
            ->withName('Daily Standup Meeting')
            ->withHighPriority()
            ->create(BeforeDueBy::fifteenMinutes()) // Create 15 minutes before due
            ->due(Schedule::create()->daily()->at('09:30'))
            ->automaticallyDelete(AfterDueBy::oneDay()->andIsComplete()));

        // Check that todo is ready to be created at 09:15 (15 minutes before 09:30)
        $readyToCreate = $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 09:15:00'));
        $this->assertCount(1, $readyToCreate);

        // Generate the todo instance at creation time
        $todoInstance = $readyToCreate[0]->nextInstance(Carbon::parse('2024-01-15 09:15:00'));

        $this->assertInstanceOf(Todo::class, $todoInstance);
        $this->assertEquals('Daily Standup Meeting', $todoInstance->name());
        $this->assertEquals(4, $todoInstance->priority()); // High priority
        $this->assertEquals(
            Carbon::parse('2024-01-15 09:15:00'),
            $todoInstance->createAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-15 09:30:00'),
            $todoInstance->dueAt()
        );

        // Check deletion times - should delete 1 day after due time if completed
        $this->assertEquals(
            Carbon::parse('2024-01-16 09:30:00'),
            $todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt()
        );
        $this->assertNull($todoInstance->automaticallyDeleteWhenIncompleteAndAfterDueAt());
    }

    public function testWeeklyMeetingWithCompletionAwareDeletion()
    {
        $todos = new Todos();

        // Define a weekly team meeting
        $todos->define(Definition::define()
            ->withName('Weekly Team Meeting')
            ->withMediumPriority()
            ->due(Schedule::create()->weekly()->mondays()->at('10:00'))
            ->automaticallyDelete(AfterExistingFor::oneWeek()->andIsIncomplete()));

        // Check at Monday 10:00
        $mondayMorning = Carbon::parse('2024-01-15 10:00:00'); // 2024-01-15 is a Monday
        $readyToCreate = $todos->readyToBeCreatedAt($mondayMorning);
        $this->assertCount(1, $readyToCreate);

        $todoInstance = $readyToCreate[0]->nextInstance($mondayMorning);

        $this->assertEquals('Weekly Team Meeting', $todoInstance->name());
        $this->assertEquals(3, $todoInstance->priority()); // Medium priority

        // Should delete 1 week after creation, but only if incomplete
        $this->assertEquals(
            Carbon::parse('2024-01-22 10:00:00'),
            $todoInstance->automaticallyDeleteWhenIncompleteAndAfterExistingAt()
        );
        $this->assertNull($todoInstance->automaticallyDeleteWhenCompleteAndAfterExistingAt());
    }

    public function testMultipleTodosReadyAtDifferentTimes()
    {
        $todos = new Todos();

        // Morning standup
        $todos->define(Definition::define()
            ->withName('Morning Standup')
            ->due(Schedule::create()->daily()->at('09:30'))
            ->automaticallyDelete(AfterDueBy::oneDay()));

        // Lunch reminder
        $todos->define(Definition::define()
            ->withName('Lunch Break')
            ->withLowPriority()
            ->due(Schedule::create()->daily()->at('12:00'))
            ->automaticallyDelete(AfterExistingFor::twoHours()));

        // End of day review
        $todos->define(Definition::define()
            ->withName('End of Day Review')
            ->due(Schedule::create()->daily()->at('17:00'))
            ->automaticallyDelete(AfterDueBy::oneDay()->andIsComplete()));

        // Test different times throughout the day
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 09:30:00')));
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 12:00:00')));
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 17:00:00')));

        // No todos ready at an off-time
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 15:30:00')));

        // All todos should generate instances
        $allInstances = $todos->nextInstances(Carbon::parse('2024-01-15 09:00:00'));
        $this->assertCount(3, $allInstances);

        $names = array_map(fn (Todo $todo) => $todo->name(), $allInstances);
        $this->assertContains('Morning Standup', $names);
        $this->assertContains('Lunch Break', $names);
        $this->assertContains('End of Day Review', $names);
    }

    public function testTodoWithCreateBeforeDueScheduling()
    {
        $todos = new Todos();

        // Project deadline reminder that appears 2 hours before due
        $todos->define(Definition::define()
            ->withName('Project Deadline Reminder')
            ->withHighPriority()
            ->create(BeforeDueBy::twoHours())
            ->due(Schedule::create()->daily()->at('17:00'))
            ->automaticallyDelete(AfterDueBy::oneDay()));

        // Should be ready to create at 15:00 (2 hours before 17:00)
        $createTime = Carbon::parse('2024-01-15 15:00:00');
        $readyToCreate = $todos->readyToBeCreatedAt($createTime);
        $this->assertCount(1, $readyToCreate);

        $todoInstance = $readyToCreate[0]->nextInstance($createTime);

        $this->assertEquals('Project Deadline Reminder', $todoInstance->name());
        $this->assertEquals(
            Carbon::parse('2024-01-15 15:00:00'),
            $todoInstance->createAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-15 17:00:00'),
            $todoInstance->dueAt()
        );

        // Should delete 1 day after due time (18:00 next day)
        $this->assertEquals(
            Carbon::parse('2024-01-16 17:00:00'),
            $todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt()
        );
    }

    public function testComplexDeletionScenarios()
    {
        $todos = new Todos();

        // Task with different deletion rules for completed vs incomplete
        $todos->define(Definition::define()
            ->withName('Complex Deletion Task')
            ->due(Schedule::create()->daily()->at('14:00'))
            ->automaticallyDelete(AfterDueBy::oneHour()->andIsComplete())
            ->automaticallyDelete(AfterExistingFor::oneDay()->andIsIncomplete()));

        $todoInstance = $todos->nextInstances(Carbon::parse('2024-01-15 14:00:00'))[0];

        // Should have both deletion times set
        $this->assertEquals(
            Carbon::parse('2024-01-15 15:00:00'), // 1 hour after due if completed
            $todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-16 14:00:00'), // 1 day after creation if incomplete
            $todoInstance->automaticallyDeleteWhenIncompleteAndAfterExistingAt()
        );

        $this->assertTrue($todoInstance->shouldEventuallyBeDeleted());
    }

    public function testMonthlyRecurringTodoLifecycle()
    {
        $todos = new Todos();

        // Monthly report due on the 15th of each month
        $todos->define(Definition::define()
            ->withName('Monthly Report')
            ->withHighPriority()
            ->due(Schedule::create()->monthlyOn(15, '17:00'))
            ->automaticallyDelete(AfterDueBy::oneWeek()->andIsComplete()));

        // Test that it's ready on the 15th
        $dueTime = Carbon::parse('2024-01-15 17:00:00');
        $readyToCreate = $todos->readyToBeCreatedAt($dueTime);
        $this->assertCount(1, $readyToCreate);

        $todoInstance = $readyToCreate[0]->nextInstance($dueTime);

        $this->assertEquals('Monthly Report', $todoInstance->name());
        $this->assertEquals(
            Carbon::parse('2024-01-15 17:00:00'),
            $todoInstance->createAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-15 17:00:00'),
            $todoInstance->dueAt()
        );

        // Should delete 1 week after due if completed
        $this->assertEquals(
            Carbon::parse('2024-01-22 17:00:00'),
            $todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt()
        );
    }

    public function testTodoPriorityLevels()
    {
        $todos = new Todos();

        // Add todos with different priorities
        $todos->define(Definition::define()
            ->withName('High Priority Task')
            ->withHighPriority()
            ->due(Schedule::create()->daily()->at('10:00')));

        $todos->define(Definition::define()
            ->withName('Medium Priority Task')
            ->withMediumPriority()
            ->due(Schedule::create()->daily()->at('10:00')));

        $todos->define(Definition::define()
            ->withName('Low Priority Task')
            ->withLowPriority()
            ->due(Schedule::create()->daily()->at('10:00')));

        $todos->define(Definition::define()
            ->withName('No Priority Task')
            ->withNoPriority()
            ->due(Schedule::create()->daily()->at('10:00')));

        $todos->define(Definition::define()
            ->withName('Default Priority Task')
            ->withDefaultPriority()
            ->due(Schedule::create()->daily()->at('10:00')));

        $instances = $todos->nextInstances(Carbon::parse('2024-01-15 10:00:00'));
        $this->assertCount(5, $instances);

        // Check priorities
        $priorities = array_map(fn (Todo $todo) => $todo->priority(), $instances);
        $this->assertContains(4, $priorities); // High
        $this->assertContains(3, $priorities); // Medium
        $this->assertContains(2, $priorities); // Low
        $this->assertContains(1, $priorities); // None
        $this->assertContains(null, $priorities); // Default
    }

    public function testContentHashGeneration()
    {
        $todos = new Todos();

        $todos->define(Definition::define()
            ->withName('Test Task')
            ->withDescription('Task for testing content hash')
            ->withHighPriority()
            ->due(Schedule::create()->daily()->at('10:00')));

        $todoInstance = $todos->nextInstances(Carbon::parse('2024-01-15 10:00:00'))[0];

        $contentHash = $todoInstance->contentHash();

        // Content hash should be a base64 encoded string
        $this->assertIsString($contentHash);
        $this->assertTrue(base64_decode($contentHash, true) !== false);

        // Decode and verify the content
        $decodedContent = json_decode(base64_decode($contentHash), true);
        $this->assertEquals('Test Task', $decodedContent['name']);
        $this->assertEquals('Task for testing content hash', $decodedContent['description']);
        $this->assertEquals(4, $decodedContent['priority']);
        $this->assertArrayHasKey('create', $decodedContent);
        $this->assertArrayHasKey('due', $decodedContent);
    }

    public function testNoDeletionRulesResultsInPersistentTodo()
    {
        $todos = new Todos();

        // Todo without any deletion rules
        $todos->define(Definition::define()
            ->withName('Persistent Task')
            ->due(Schedule::create()->daily()->at('10:00')));

        $todoInstance = $todos->nextInstances(Carbon::parse('2024-01-15 10:00:00'))[0];

        $this->assertFalse($todoInstance->shouldEventuallyBeDeleted());
        $this->assertNull($todoInstance->automaticallyDeleteWhenCompleteAndAfterDueAt());
        $this->assertNull($todoInstance->automaticallyDeleteWhenIncompleteAndAfterDueAt());
        $this->assertNull($todoInstance->automaticallyDeleteWhenCompleteAndAfterExistingAt());
        $this->assertNull($todoInstance->automaticallyDeleteWhenIncompleteAndAfterExistingAt());
    }

    /**
     * Demonstration of reduced verbosity using TestScenarioBuilder for lifecycle testing.
     * Compare this to the testCompleteDailyTodoLifecycle method above.
     */
    public function testLifecycleWithBuilderPattern()
    {
        $todos = new Todos();

        // OLD APPROACH (see testCompleteDailyTodoLifecycle): 6+ lines of definition setup
        // NEW APPROACH: TestScenarioBuilder (2-3 lines)
        $scenario = TestScenarioBuilder::dailyMeeting()
            ->withName('Builder Standup Meeting');

        $definition = $scenario->buildDefinition();
        $todos->define($definition);

        // Same lifecycle verification, but much cleaner setup
        $testTime = Carbon::parse('2024-01-15 09:00:00'); // Meeting time
        $readyToCreate = $todos->readyToBeCreatedAt($testTime);
        
        $this->assertCount(1, $readyToCreate);
        
        $todoInstance = $readyToCreate[0]->nextInstance($testTime);
        
        $this->assertInstanceOf(Todo::class, $todoInstance);
        $this->assertEquals('Builder Standup Meeting', $todoInstance->name());
        $this->assertEquals(4, $todoInstance->priority()); // High priority from dailyMeeting preset
        $this->assertEquals($testTime, $todoInstance->dueAt());
    }
}
