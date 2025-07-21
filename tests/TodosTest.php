<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\FinalizedDefinition;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Todo;
use Simensen\EphemeralTodos\Todos;

class TodosTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow('2024-01-15 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function testCanCreateEmptyTodosCollection()
    {
        $todos = new Todos();

        $this->assertInstanceOf(Todos::class, $todos);
    }

    public function testCanDefineTodoUsingDefinitionObject()
    {
        $todos = new Todos();
        $definition = Definition::define()
            ->withName('Test Task')
            ->withHighPriority()
            ->due(Schedule::create()->daily()->at('14:00'));

        $todos->define($definition);

        // Test that the definition was added by checking ready to be created
        $readyToCreate = $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 14:00:00'));
        $this->assertCount(1, $readyToCreate);
        $this->assertInstanceOf(FinalizedDefinition::class, $readyToCreate[0]);
    }

    public function testCanDefineTodoUsingFinalizedDefinition()
    {
        $todos = new Todos();
        $definition = Definition::define()
            ->withName('Test Task')
            ->withMediumPriority()
            ->due(Schedule::create()->daily()->at('16:00'))
            ->finalize();

        $todos->define($definition);

        $readyToCreate = $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 16:00:00'));
        $this->assertCount(1, $readyToCreate);
    }

    public function testCanDefineTodoUsingCallable()
    {
        $todos = new Todos();

        $todos->define(function (Definition $definition) {
            return $definition
                ->withName('Callable Task')
                ->withLowPriority()
                ->due(Schedule::create()->daily()->at('10:30'));
        });

        $readyToCreate = $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 10:30:00'));
        $this->assertCount(1, $readyToCreate);
    }

    public function testCanDefineMultipleTodos()
    {
        $todos = new Todos();

        $todos->define(Definition::define()
            ->withName('Task 1')
            ->due(Schedule::create()->daily()->at('11:00')));

        $todos->define(Definition::define()
            ->withName('Task 2')
            ->due(Schedule::create()->daily()->at('12:00')));

        $todos->define(Definition::define()
            ->withName('Task 3')
            ->due(Schedule::create()->daily()->at('13:00')));

        // All should be ready at their respective times
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 11:00:00')));
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 12:00:00')));
        $this->assertCount(1, $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 13:00:00')));

        // None should be ready right now (10:00)
        $this->assertCount(0, $todos->readyToBeCreatedAt(Carbon::now()));
    }

    public function testReadyToBeCreatedAtFiltersCorrectly()
    {
        $todos = new Todos();

        // Task due at 11:00
        $todos->define(Definition::define()
            ->withName('Task 1')
            ->due(Schedule::create()->daily()->at('11:00')));

        // Task due at 12:00
        $todos->define(Definition::define()
            ->withName('Task 2')
            ->due(Schedule::create()->daily()->at('12:00')));

        // Check at 11:00 - only first task should be ready
        $readyAtEleven = $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 11:00:00'));
        $this->assertCount(1, $readyAtEleven);

        // Check at 12:00 - only second task should be ready
        $readyAtTwelve = $todos->readyToBeCreatedAt(Carbon::parse('2024-01-15 12:00:00'));
        $this->assertCount(1, $readyAtTwelve);

        // Check current time - no tasks should be ready
        $readyNow = $todos->readyToBeCreatedAt(Carbon::now());
        $this->assertCount(0, $readyNow);
    }

    public function testNextInstancesGeneratesTodoObjects()
    {
        $todos = new Todos();

        $todos->define(Definition::define()
            ->withName('Instance Task 1')
            ->withHighPriority()
            ->due(Schedule::create()->daily()->at('11:00'))
            ->automaticallyDelete(AfterDueBy::oneDay()));

        $todos->define(Definition::define()
            ->withName('Instance Task 2')
            ->withMediumPriority()
            ->due(Schedule::create()->daily()->at('12:00'))
            ->automaticallyDelete(AfterExistingFor::oneWeek()));

        $instances = $todos->nextInstances(Carbon::now());

        $this->assertCount(2, $instances);
        $this->assertInstanceOf(Todo::class, $instances[0]);
        $this->assertInstanceOf(Todo::class, $instances[1]);

        // Check that the todos have the expected properties
        $this->assertEquals('Instance Task 1', $instances[0]->name());
        $this->assertEquals('Instance Task 2', $instances[1]->name());
    }

    public function testNextInstancesWithScheduledTodos()
    {
        $todos = new Todos();

        // Add a daily task
        $todos->define(Definition::define()
            ->withName('Daily Task')
            ->due(Schedule::create()->daily()->at('14:00'))
            ->automaticallyDelete(AfterDueBy::oneDay()));

        // Add a weekly task
        $todos->define(Definition::define()
            ->withName('Weekly Task')
            ->due(Schedule::create()->weekly()->mondays()->at('09:00'))
            ->automaticallyDelete(AfterExistingFor::oneWeek()));

        $instances = $todos->nextInstances(Carbon::now());

        $this->assertCount(2, $instances);

        // Should generate Todo instances even if not currently due
        foreach ($instances as $instance) {
            $this->assertInstanceOf(Todo::class, $instance);
        }
    }

    public function testReadyToBeCreatedAtWithDefaultTime()
    {
        $todos = new Todos();

        // Task that should be ready right now (due at current time)
        $todos->define(Definition::define()
            ->withName('Due Now Task')
            ->due(Schedule::create()->daily()->at('10:00')));

        // Should use current time if no time specified
        $readyNow = $todos->readyToBeCreatedAt();
        $this->assertCount(1, $readyNow);
    }

    public function testNextInstancesWithDefaultTime()
    {
        $todos = new Todos();

        $todos->define(Definition::define()
            ->withName('Test Task')
            ->due(Schedule::create()->daily()->at('11:00')));

        // Should use current time if no time specified
        $instances = $todos->nextInstances();
        $this->assertCount(1, $instances);
        $this->assertInstanceOf(Todo::class, $instances[0]);
    }

    public function testComplexCollectionScenario()
    {
        $todos = new Todos();

        // Add various types of todos
        $todos->define(Definition::define()
            ->withName('Immediate Task')
            ->due(Schedule::create()->daily()->at('10:00'))
            ->automaticallyDelete(AfterDueBy::oneHour()));

        $todos->define(Definition::define()
            ->withName('Future Task')
            ->due(Schedule::create()->daily()->at('14:00'))
            ->automaticallyDelete(AfterExistingFor::oneDay()));

        $todos->define(Definition::define()
            ->withName('Scheduled Task')
            ->due(Schedule::create()->daily()->at('18:00'))
            ->automaticallyDelete(AfterDueBy::oneDay()->andIsComplete()));

        $todos->define(function (Definition $def) {
            return $def
                ->withName('Callable Task')
                ->withNoPriority()
                ->due(Schedule::create()->daily()->at('16:00'))
                ->automaticallyDelete(AfterExistingFor::oneWeek()->andIsIncomplete());
        });

        // Test ready to be created filtering
        $readyNow = $todos->readyToBeCreatedAt(Carbon::now());
        $this->assertGreaterThanOrEqual(1, count($readyNow)); // At least the immediate task

        // Test instance generation
        $instances = $todos->nextInstances(Carbon::now());
        $this->assertCount(4, $instances);

        // All should be Todo instances
        foreach ($instances as $instance) {
            $this->assertInstanceOf(Todo::class, $instance);
        }

        // Check that names were preserved
        $names = array_map(fn (Todo $todo) => $todo->name(), $instances);
        $this->assertContains('Immediate Task', $names);
        $this->assertContains('Future Task', $names);
        $this->assertContains('Scheduled Task', $names);
        $this->assertContains('Callable Task', $names);
    }
}
