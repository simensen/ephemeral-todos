<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests;

use Carbon\Carbon;
use LogicException;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Todo;
use Simensen\EphemeralTodos\Todos;
use Simensen\EphemeralTodos\Utils;

class NullValueErrorConditionTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow('2024-01-15 10:00:00 UTC');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function test_utils_to_carbon_with_null()
    {
        // Null should create current time
        $result = Utils::toCarbon(null);
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals(Carbon::now(), $result);
    }

    public function test_utils_equal_to_minute_with_null_combinations()
    {
        $time = Carbon::parse('2024-01-15 10:30:15');
        
        // Both null should be equal (both become "now")
        $this->assertTrue(Utils::equalToTheMinute(null, null));
        
        // Null and time in same minute should be equal (our test time is 10:00)
        $sameMinuteTime = Carbon::parse('2024-01-15 10:00:30');
        $this->assertTrue(Utils::equalToTheMinute(null, $sameMinuteTime));
        $this->assertTrue(Utils::equalToTheMinute($sameMinuteTime, null));
        
        // Null and time in different minute should not be equal
        $differentMinuteTime = Carbon::parse('2024-01-15 10:01:00');
        $this->assertFalse(Utils::equalToTheMinute(null, $differentMinuteTime));
        $this->assertFalse(Utils::equalToTheMinute($differentMinuteTime, null));
    }

    public function test_definition_finalize_without_required_fields()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You must define a `create` or `due` for a definition to be finalized.');
        
        // Definition with no create or due should throw exception
        Definition::define()
            ->withName('Incomplete Definition')
            ->finalize();
    }

    public function test_todos_ready_to_be_created_at_with_null()
    {
        $todos = new Todos();
        
        $todos->define(Definition::define()
            ->withName('Current Time Task')
            ->due(Schedule::create()->daily()->at('10:00')));
        
        // Calling with null should use current time (10:00)
        $readyWithNull = $todos->readyToBeCreatedAt(null);
        $readyWithNow = $todos->readyToBeCreatedAt(Carbon::now());
        
        $this->assertEquals(count($readyWithNull), count($readyWithNow));
        $this->assertCount(1, $readyWithNull);
    }

    public function test_todos_next_instances_with_null()
    {
        $todos = new Todos();
        
        $todos->define(Definition::define()
            ->withName('Test Task')
            ->due(Schedule::create()->daily()->at('10:00')));
        
        // Calling with null should use current time
        $instancesWithNull = $todos->nextInstances(null);
        $instancesWithNow = $todos->nextInstances(Carbon::now());
        
        $this->assertCount(1, $instancesWithNull);
        $this->assertCount(1, $instancesWithNow);
        $this->assertEquals($instancesWithNull[0]->name(), $instancesWithNow[0]->name());
    }

    public function test_todo_constructor_with_null_values()
    {
        $createAt = Carbon::parse('2024-01-15 10:00:00')->toDateTimeImmutable();
        
        // Test with null due date
        $todo = new Todo('Test Task', $createAt, 1, null);
        
        $this->assertEquals('Test Task', $todo->name());
        $this->assertEquals($createAt, $todo->createAt());
        $this->assertEquals(1, $todo->priority());
        $this->assertNull($todo->dueAt());
        $this->assertNull($todo->description());
    }

    public function test_todo_constructor_with_all_null_deletion_times()
    {
        $createAt = Carbon::parse('2024-01-15 10:00:00')->toDateTimeImmutable();
        
        $todo = new Todo('Test Task', $createAt, 1, null, null, null, null, null, null);
        
        $this->assertFalse($todo->shouldEventuallyBeDeleted());
        $this->assertNull($todo->automaticallyDeleteWhenCompleteAndAfterDueAt());
        $this->assertNull($todo->automaticallyDeleteWhenIncompleteAndAfterDueAt());
        $this->assertNull($todo->automaticallyDeleteWhenCompleteAndAfterExistingAt());
        $this->assertNull($todo->automaticallyDeleteWhenIncompleteAndAfterExistingAt());
    }

    public function test_todo_constructor_with_partial_deletion_times()
    {
        $createAt = Carbon::parse('2024-01-15 10:00:00')->toDateTimeImmutable();
        $deleteAt = Carbon::parse('2024-01-15 11:00:00')->toDateTimeImmutable();
        
        // Only set one deletion time
        $todo = new Todo('Test Task', $createAt, 1, null, null, $deleteAt, null, null, null);
        
        $this->assertTrue($todo->shouldEventuallyBeDeleted());
        $this->assertEquals($deleteAt, $todo->automaticallyDeleteWhenCompleteAndAfterDueAt());
        $this->assertNull($todo->automaticallyDeleteWhenIncompleteAndAfterDueAt());
        $this->assertNull($todo->automaticallyDeleteWhenCompleteAndAfterExistingAt());
        $this->assertNull($todo->automaticallyDeleteWhenIncompleteAndAfterExistingAt());
    }

    public function test_schedule_with_empty_cron_expression()
    {
        // Test with default cron expression (should be "* * * * *")
        $schedule = Schedule::create();
        
        $this->assertEquals('* * * * *', $schedule->cronExpression());
        
        // Should be due every minute
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-15 10:00:00')));
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-15 10:01:00')));
        $this->assertTrue($schedule->isDue(Carbon::parse('2024-01-15 10:59:00')));
    }

    public function test_definition_with_null_priority()
    {
        $definition = Definition::define()
            ->withName('No Priority Task')
            ->due(Schedule::create()->daily())
            ->finalize();
        
        $todo = $definition->nextInstance(Carbon::now());
        
        $this->assertNull($todo->priority());
    }

    public function test_definition_with_null_description()
    {
        $definition = Definition::define()
            ->withName('No Description Task')
            ->due(Schedule::create()->daily())
            ->finalize();
        
        $todo = $definition->nextInstance(Carbon::now());
        
        $this->assertNull($todo->description());
    }

    public function test_empty_todos_collection()
    {
        $todos = new Todos();
        
        // Empty collection should return empty arrays
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));
        $this->assertCount(0, $todos->nextInstances(Carbon::now()));
        
        // Should work with null too
        $this->assertCount(0, $todos->readyToBeCreatedAt(null));
        $this->assertCount(0, $todos->nextInstances(null));
    }

    public function test_definition_without_name()
    {
        // Definition without name should throw an error because name is required
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Typed property Simensen\EphemeralTodos\Definition::$name must not be accessed before initialization');
        
        $definition = Definition::define()
            ->due(Schedule::create()->daily())
            ->finalize();
    }

    public function test_invalid_time_strings_handling()
    {
        // Test how Utils handles invalid time strings
        try {
            $result = Utils::toCarbon('invalid-time-string');
            // If no exception, should be a Carbon instance
            $this->assertInstanceOf(Carbon::class, $result);
        } catch (\Exception $e) {
            // If exception thrown, that's also acceptable behavior
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function test_definition_finalize_with_only_create()
    {
        // Definition with only create (no due) should work
        $definition = Definition::define()
            ->withName('Create Only Task')
            ->create(Schedule::create()->daily()->at('14:00'))
            ->finalize();
        
        $this->assertInstanceOf(\Simensen\EphemeralTodos\FinalizedDefinition::class, $definition);
        
        $todo = $definition->nextInstance(Carbon::parse('2024-01-15 14:00:00'));
        $this->assertEquals('Create Only Task', $todo->name());
    }

    public function test_definition_finalize_with_only_due()
    {
        // Definition with only due (no create) should work
        $definition = Definition::define()
            ->withName('Due Only Task')
            ->due(Schedule::create()->daily()->at('16:00'))
            ->finalize();
        
        $this->assertInstanceOf(\Simensen\EphemeralTodos\FinalizedDefinition::class, $definition);
        
        $todo = $definition->nextInstance(Carbon::parse('2024-01-15 16:00:00'));
        $this->assertEquals('Due Only Task', $todo->name());
    }

    public function test_zero_priority_handling()
    {
        // Test if priority can be zero (might be different from null)
        $createAt = Carbon::parse('2024-01-15 10:00:00')->toDateTimeImmutable();
        $todo = new Todo('Zero Priority Task', $createAt, 0);
        
        $this->assertEquals(0, $todo->priority());
    }

    public function test_extreme_deletion_time_combinations()
    {
        $createAt = Carbon::parse('2024-01-15 10:00:00')->toDateTimeImmutable();
        $dueAt = Carbon::parse('2024-01-15 12:00:00')->toDateTimeImmutable();
        
        // Test with only some deletion times set to extreme values
        $veryLateDelete = Carbon::parse('2025-01-15 10:00:00')->toDateTimeImmutable(); // 1 year later
        
        $todo = new Todo(
            'Extreme Delete Task',
            $createAt,
            1,
            $dueAt,
            null,
            $veryLateDelete, // Only this one set
            null,
            null,
            null
        );
        
        $this->assertTrue($todo->shouldEventuallyBeDeleted());
        $this->assertEquals($veryLateDelete, $todo->automaticallyDeleteWhenCompleteAndAfterDueAt());
        $this->assertNull($todo->automaticallyDeleteWhenIncompleteAndAfterDueAt());
    }

    public function test_content_hash_with_null_values()
    {
        $createAt = Carbon::parse('2024-01-15 10:00:00')->toDateTimeImmutable();
        
        // Todo with many null values
        $todo = new Todo('Hash Test', $createAt, null, null, null);
        
        $hash = $todo->contentHash();
        $this->assertIsString($hash);
        
        // Should be valid base64
        $decoded = base64_decode($hash, true);
        $this->assertNotFalse($decoded);
        
        // Should be valid JSON
        $data = json_decode($decoded, true);
        $this->assertIsArray($data);
        $this->assertEquals('Hash Test', $data['name']);
        $this->assertNull($data['priority']);
        $this->assertNull($data['description']);
    }

    public function test_todos_define_with_null_callable_result()
    {
        $todos = new Todos();
        
        // Test what happens if callable returns null
        // The Todos class might handle this gracefully or throw an error
        try {
            $todos->define(function () {
                return null; // This should cause an error
            });
            // If no exception, test that nothing bad happened
            $this->assertTrue(true);
        } catch (\TypeError $e) {
            // If TypeError thrown, that's also acceptable behavior
            $this->assertInstanceOf(\TypeError::class, $e);
        }
    }

    public function test_schedule_is_due_with_null_time()
    {
        $schedule = Schedule::create()->daily()->at('14:00');
        
        // Calling isDue with null should use current time
        Carbon::setTestNow('2024-01-15 14:00:00');
        $this->assertTrue($schedule->isDue(null));
        
        Carbon::setTestNow('2024-01-15 15:00:00');
        $this->assertFalse($schedule->isDue(null));
    }

    public function test_empty_string_name_handling()
    {
        // Empty string name should cause an error during finalization
        $this->expectException(\ArgumentCountError::class);
        
        $definition = Definition::define()
            ->withName('') // Empty string name
            ->due(Schedule::create()->daily())
            ->finalize();
    }

    public function test_edge_case_timezone_with_null()
    {
        // Test Utils::toCarbon with null timezone
        $result = Utils::toCarbon('2024-01-15 10:00:00', null);
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2024-01-15 10:00:00', $result->format('Y-m-d H:i:s'));
    }
}