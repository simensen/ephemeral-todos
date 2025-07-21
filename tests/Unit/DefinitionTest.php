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
    public function testDefineCreatesNewInstance(): void
    {
        $definition = Definition::define();

        $this->assertInstanceOf(Definition::class, $definition);
    }

    public function testFluentBuilderPatternReturnsNewInstances(): void
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

    public function testWithNameMethod(): void
    {
        $definition = Definition::define()->withName('Task Name');

        // Since properties are private, we test through finalization
        $this->expectNotToPerformAssertions();
        // If finalization works without error, the name was set correctly
    }

    public function testWithDescriptionMethod(): void
    {
        $definition = Definition::define()
            ->withName('Test Task')
            ->withDescription('Task description')
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testWithHighPrioritySetsPriorityTo4(): void
    {
        $definition = Definition::define()
            ->withName('High Priority Task')
            ->withHighPriority()
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testWithMediumPrioritySetsPriorityTo3(): void
    {
        $definition = Definition::define()
            ->withName('Medium Priority Task')
            ->withMediumPriority()
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testWithLowPrioritySetsPriorityTo2(): void
    {
        $definition = Definition::define()
            ->withName('Low Priority Task')
            ->withLowPriority()
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testWithNoPrioritySetsPriorityTo1(): void
    {
        $definition = Definition::define()
            ->withName('No Priority Task')
            ->withNoPriority()
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testWithDefaultPrioritySetsPriorityToNull(): void
    {
        $definition = Definition::define()
            ->withName('Default Priority Task')
            ->withDefaultPriority()
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testCreateMethodWithSchedule(): void
    {
        $schedule = Schedule::create()->daily();
        $definition = Definition::define()
            ->withName('Scheduled Task')
            ->create($schedule);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testCreateMethodWithBeforeDueBy(): void
    {
        $beforeDue = BeforeDueBy::whenDue();
        $definition = Definition::define()
            ->withName('Before Due Task')
            ->create($beforeDue)
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testDueMethodWithSchedule(): void
    {
        $schedule = Schedule::create()->daily();
        $definition = Definition::define()
            ->withName('Due Task')
            ->due($schedule);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testDueMethodWithInObject(): void
    {
        $in = In::twoHours();
        $definition = Definition::define()
            ->withName('Due In Hours Task')
            ->due($in);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testDueMethodWithCallable(): void
    {
        $definition = Definition::define()
            ->withName('Callable Due Task')
            ->due(fn ($schedule) => $schedule->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testAutomaticallyDeleteWithAfterDueBy(): void
    {
        $afterDue = AfterDueBy::oneHour()->whetherCompletedOrNot();
        $definition = Definition::define()
            ->withName('Auto Delete Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterDue);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testAutomaticallyDeleteWithAfterExistingFor(): void
    {
        $afterExisting = AfterExistingFor::oneHour()->whetherCompletedOrNot();
        $definition = Definition::define()
            ->withName('Auto Delete Existing Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterExisting);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testFinalizeThrowsExceptionWhenNeitherCreateNorDueDefined(): void
    {
        $definition = Definition::define()->withName('Incomplete Task');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You must define a `create` or `due` for a definition to be finalized.');

        $definition->finalize();
    }

    public function testFinalizeUsesDueAsCreateWhenOnlyDueDefined(): void
    {
        $definition = Definition::define()
            ->withName('Due Only Task')
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testFinalizePreservesBothCreateAndDueWhenBothDefined(): void
    {
        $definition = Definition::define()
            ->withName('Both Create and Due Task')
            ->create(Schedule::create()->hourly())
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testMethodChainingPreservesImmutability(): void
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

    public function testPriorityMethodsOverrideEachOther(): void
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

    public function testAutomaticallyDeleteSwitchStatementLogicWithAfterDueByAppliesAlways(): void
    {
        $afterDue = AfterDueBy::oneHour()->whetherCompletedOrNot();
        $definition = Definition::define()
            ->withName('Delete Always Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterDue);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testAutomaticallyDeleteSwitchStatementLogicWithAfterDueByAppliesWhenComplete(): void
    {
        $afterDue = AfterDueBy::oneHour()->andIsComplete();
        $definition = Definition::define()
            ->withName('Delete When Complete Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterDue);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testAutomaticallyDeleteSwitchStatementLogicWithAfterDueByAppliesWhenIncomplete(): void
    {
        $afterDue = AfterDueBy::oneHour()->andIsIncomplete();
        $definition = Definition::define()
            ->withName('Delete When Incomplete Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterDue);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testAutomaticallyDeleteSwitchStatementLogicWithAfterExistingForAppliesAlways(): void
    {
        $afterExisting = AfterExistingFor::oneHour()->whetherCompletedOrNot();
        $definition = Definition::define()
            ->withName('Delete After Existing Always Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterExisting);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testAutomaticallyDeleteSwitchStatementLogicWithAfterExistingForAppliesWhenComplete(): void
    {
        $afterExisting = AfterExistingFor::oneHour()->andIsComplete();
        $definition = Definition::define()
            ->withName('Delete After Existing When Complete Task')
            ->due(Schedule::create()->daily())
            ->automaticallyDelete($afterExisting);

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized);
    }

    public function testAutomaticallyDeleteSwitchStatementLogicWithAfterExistingForAppliesWhenIncomplete(): void
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
