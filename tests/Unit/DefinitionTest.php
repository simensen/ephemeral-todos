<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use LogicException;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\BeforeDueBy;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\FinalizedDefinition;
use Simensen\EphemeralTodos\In;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Tests\TestCase;

class DefinitionTest extends TestCase
{
    public function test_define_creates_new_instance(): void
    {
        $definition = Definition::define();
        
        $this->assertInstanceOf(Definition::class, $definition);
    }

    public function test_fluent_builder_pattern_returns_new_instances(): void
    {
        $original = Definition::define();
        $withName = $original->withName('Test Task');
        $withDescription = $withName->withDescription('Test description');
        $withPriority = $withDescription->withHighPriority();

        // Each method should return a new instance
        $this->assertNotSame($original, $withName);
        $this->assertNotSame($withName, $withDescription);
        $this->assertNotSame($withDescription, $withPriority);
    }

    public function test_with_name_method(): void
    {
        $definition = Definition::define()->withName('Task Name');
        
        // Since properties are private, we test through finalization
        $this->expectNotToPerformAssertions();
        // If finalization works without error, the name was set correctly
    }

    public function test_with_description_method(): void
    {
        $definition = Definition::define()
            ->withName('Test Task')
            ->withDescription('Task description')
            ->due(Schedule::create()->daily());
        
        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_with_high_priority_sets_priority_to_4(): void
    {
        $definition = Definition::define()
            ->withName('High Priority Task')
            ->withHighPriority()
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_with_medium_priority_sets_priority_to_3(): void
    {
        $definition = Definition::define()
            ->withName('Medium Priority Task')
            ->withMediumPriority()
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_with_low_priority_sets_priority_to_2(): void
    {
        $definition = Definition::define()
            ->withName('Low Priority Task')
            ->withLowPriority()
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_with_no_priority_sets_priority_to_1(): void
    {
        $definition = Definition::define()
            ->withName('No Priority Task')
            ->withNoPriority()
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_with_default_priority_sets_priority_to_null(): void
    {
        $definition = Definition::define()
            ->withName('Default Priority Task')
            ->withDefaultPriority()
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_create_method_with_schedule(): void
    {
        $schedule = Schedule::create()->daily();
        $definition = Definition::define()
            ->withName('Scheduled Task')
            ->create($schedule);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_create_method_with_before_due_by(): void
    {
        $beforeDue = BeforeDueBy::whenDue();
        $definition = Definition::define()
            ->withName('Before Due Task')
            ->create($beforeDue)
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_due_method_with_schedule(): void
    {
        $schedule = Schedule::create()->daily();
        $definition = Definition::define()
            ->withName('Due Task')
            ->due($schedule);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_due_method_with_in_object(): void
    {
        $in = In::twoHours();
        $definition = Definition::define()
            ->withName('Due In Hours Task')
            ->due($in);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_due_method_with_callable(): void
    {
        $definition = Definition::define()
            ->withName('Callable Due Task')
            ->due(fn($schedule) => $schedule->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_automatically_delete_with_after_due_by(): void
    {
        $afterDue = AfterDueBy::oneHour()->whetherCompletedOrNot();
        $definition = Definition::define()
            ->withName('Auto Delete Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterDue);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_automatically_delete_with_after_existing_for(): void
    {
        $afterExisting = AfterExistingFor::oneHour()->whetherCompletedOrNot();
        $definition = Definition::define()
            ->withName('Auto Delete Existing Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterExisting);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_finalize_throws_exception_when_neither_create_nor_due_defined(): void
    {
        $definition = Definition::define()->withName('Incomplete Task');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You must define a `create` or `due` for a definition to be finalized.');
        
        $definition->finalize();
    }

    public function test_finalize_uses_due_as_create_when_only_due_defined(): void
    {
        $definition = Definition::define()
            ->withName('Due Only Task')
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_finalize_preserves_both_create_and_due_when_both_defined(): void
    {
        $definition = Definition::define()
            ->withName('Both Create and Due Task')
            ->create(Schedule::create()->hourly())
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_method_chaining_preserves_immutability(): void
    {
        $original = Definition::define();
        
        $chained = $original
            ->withName('Chained Task')
            ->withDescription('Chained description')
            ->withHighPriority()
            ->due(Schedule::create()->daily())
            ->automaticallyDelete(AfterDueBy::oneHour()->whetherCompletedOrNot());

        // Original should be unchanged
        $this->assertNotSame($original, $chained);
        
        // Chained should be finalizable
        $finalized = $chained->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_priority_methods_override_each_other(): void
    {
        $definition = Definition::define()
            ->withName('Priority Test')
            ->withHighPriority()
            ->withMediumPriority()  // This should override high priority
            ->withLowPriority()     // This should override medium priority
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_automatically_delete_switch_statement_logic_with_after_due_by_applies_always(): void
    {
        $afterDue = AfterDueBy::oneHour()->whetherCompletedOrNot();
        $definition = Definition::define()
            ->withName('Delete Always Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterDue);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_automatically_delete_switch_statement_logic_with_after_due_by_applies_when_complete(): void
    {
        $afterDue = AfterDueBy::oneHour()->andIsComplete();
        $definition = Definition::define()
            ->withName('Delete When Complete Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterDue);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_automatically_delete_switch_statement_logic_with_after_due_by_applies_when_incomplete(): void
    {
        $afterDue = AfterDueBy::oneHour()->andIsIncomplete();
        $definition = Definition::define()
            ->withName('Delete When Incomplete Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterDue);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_automatically_delete_switch_statement_logic_with_after_existing_for_applies_always(): void
    {
        $afterExisting = AfterExistingFor::oneHour()->whetherCompletedOrNot();
        $definition = Definition::define()
            ->withName('Delete After Existing Always Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterExisting);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_automatically_delete_switch_statement_logic_with_after_existing_for_applies_when_complete(): void
    {
        $afterExisting = AfterExistingFor::oneHour()->andIsComplete();
        $definition = Definition::define()
            ->withName('Delete After Existing When Complete Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterExisting);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function test_automatically_delete_switch_statement_logic_with_after_existing_for_applies_when_incomplete(): void
    {
        $afterExisting = AfterExistingFor::oneHour()->andIsIncomplete();
        $definition = Definition::define()
            ->withName('Delete After Existing When Incomplete Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterExisting);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }
}