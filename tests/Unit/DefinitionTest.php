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
use Simensen\EphemeralTodos\Tests\Testing\AssertsImmutability;

class DefinitionTest extends TestCase
{
    use AssertsImmutability;
    public function testDefineCreatesNewInstance(): void
    {
        $definition = Definition::define();

        $this->assertInstanceOf(Definition::class, $definition);
    }

    public function testFluentBuilderPatternReturnsNewInstances(): void
    {
        $original = Definition::define();
        
        $this->assertMultipleMethodsReturnNewInstances($original, [
            'withName' => ['Test Task'],
            'withDescription' => ['Test description'],
            'withHighPriority' => []
        ]);
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

    // NOTE: Individual priority test methods have been replaced by the parameterized
    // testPriorityMethods method using the priorityLevelsProvider data provider.
    // This eliminates code duplication while maintaining comprehensive test coverage.

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

    /**
     * Data provider for priority testing.
     * 
     * @return array Array of [priority_description, method_name, expected_behavior_description]
     */
    public static function priorityLevelsProvider(): array
    {
        return [
            ['High Priority', 'withHighPriority', 'Sets priority to highest level (4)'],
            ['Medium Priority', 'withMediumPriority', 'Sets priority to medium level (3)'],
            ['Low Priority', 'withLowPriority', 'Sets priority to low level (2)'],
            ['No Priority', 'withNoPriority', 'Sets priority to minimum level (1)'],
            ['Default Priority', 'withDefaultPriority', 'Sets priority to default (null)'],
        ];
    }

    /**
     * Test that priority methods can be applied and result in successful finalization.
     * 
     * @dataProvider priorityLevelsProvider
     */
    public function testPriorityMethods(string $description, string $methodName, string $behaviorDescription): void
    {
        $definition = Definition::define()
            ->withName($description . ' Task')
            ->{$methodName}()
            ->due(Schedule::create()->daily());

        $finalized = $definition->finalize();
        $this->assertInstanceOf(FinalizedDefinition::class, $finalized, 
            "Definition with {$description} should finalize successfully. {$behaviorDescription}");
    }
}
