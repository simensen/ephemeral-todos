<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Testing;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Simensen\EphemeralTodos\Definition;

class EphemeralTodoTestScenario
{
    private ?TestScenarioBuilder $builder = null;

    public function __construct(
        public Definition $definition,
        public Carbon     $when,
        public ?Carbon    $createsAt = null,
        public ?Carbon    $dueAt = null,
        public ?Carbon    $automaticallyDeleteWhenCompleteAndAfterDueAt = null,
        public ?Carbon    $automaticallyDeleteWhenIncompleteAndAfterDueAt = null,
        public ?Carbon    $automaticallyDeleteWhenCompleteAndAfterExistingAt = null,
        public ?Carbon    $automaticallyDeleteWhenIncompleteAndAfterExistingAt = null,
    )
    {
    }

    /**
     * Create an EphemeralTodoTestScenario from a TestScenarioBuilder.
     * This provides a migration path from the new builder to the legacy format.
     */
    public static function fromTestScenarioBuilder(TestScenarioBuilder $builder, Carbon $when): self
    {
        $definition = $builder->buildDefinition();
        $finalizedDefinition = $definition->finalize();
        $todo = $finalizedDefinition->currentInstance($when);

        $scenario = new self(
            definition: $definition,
            when: $when,
            createsAt: $todo?->createAt() ? Carbon::instance($todo->createAt()) : null,
            dueAt: $todo?->dueAt() ? Carbon::instance($todo->dueAt()) : null,
            automaticallyDeleteWhenCompleteAndAfterDueAt: $todo?->automaticallyDeleteWhenCompleteAndAfterDueAt() ? Carbon::instance($todo->automaticallyDeleteWhenCompleteAndAfterDueAt()) : null,
            automaticallyDeleteWhenIncompleteAndAfterDueAt: $todo?->automaticallyDeleteWhenIncompleteAndAfterDueAt() ? Carbon::instance($todo->automaticallyDeleteWhenIncompleteAndAfterDueAt()) : null,
            automaticallyDeleteWhenCompleteAndAfterExistingAt: $todo?->automaticallyDeleteWhenCompleteAndAfterExistingAt() ? Carbon::instance($todo->automaticallyDeleteWhenCompleteAndAfterExistingAt()) : null,
            automaticallyDeleteWhenIncompleteAndAfterExistingAt: $todo?->automaticallyDeleteWhenIncompleteAndAfterExistingAt() ? Carbon::instance($todo->automaticallyDeleteWhenIncompleteAndAfterExistingAt()) : null,
        );

        $scenario->builder = $builder;
        return $scenario;
    }

    /**
     * Get the TestScenarioBuilder used to create this scenario (if any).
     * This allows access to enhanced features while maintaining compatibility.
     */
    public function getBuilder(): ?TestScenarioBuilder
    {
        return $this->builder;
    }

    /**
     * Check if this scenario was created from a TestScenarioBuilder.
     */
    public function hasEnhancedFeatures(): bool
    {
        return $this->builder !== null;
    }

    /**
     * Get timezone information if available from the builder.
     */
    public function getTimezone(): ?string
    {
        return $this->builder?->getTimezone();
    }

    /**
     * Get priority information if available from the builder.
     */
    public function getPriority(): ?string
    {
        return $this->builder?->getPriority();
    }

    /**
     * Get business hours information if available from the builder.
     */
    public function getBusinessHours(): array
    {
        if ($this->builder === null) {
            return [];
        }

        return [
            'start' => $this->builder->getBusinessHoursStart(),
            'end' => $this->builder->getBusinessHoursEnd(),
        ];
    }

    /**
     * Test if the scenario time is within business hours (if configured).
     */
    public function isWithinBusinessHours(): ?bool
    {
        return $this->builder?->isWithinBusinessHours($this->when);
    }

    /**
     * Convert the scenario time to a different timezone (if builder available).
     */
    public function convertToTimezone(string $targetTimezone): ?CarbonInterface
    {
        return $this->builder?->convertToTimezone($this->when, $targetTimezone);
    }

    /**
     * Check if this scenario crosses various boundaries (if builder available).
     */
    public function crossesBoundaries(): array
    {
        if ($this->builder === null) {
            return [];
        }

        $dayAfter = $this->when->copy()->addDay();
        
        return [
            'day' => $this->builder->crossesDayBoundary($this->when, $dayAfter),
            'month' => $this->builder->crossesMonthBoundary($this->when, $dayAfter),
            'year' => $this->builder->crossesYearBoundary($this->when, $dayAfter),
            'quarter' => $this->builder->crossesQuarterBoundary($this->when, $dayAfter),
            'weekend' => $this->builder->crossesWeekendBoundary($this->when, $dayAfter),
        ];
    }

    /**
     * Create a legacy scenario using the fluent TestScenarioBuilder API.
     * This provides a migration helper for updating existing tests.
     */
    public static function build(): TestScenarioBuilder
    {
        return TestScenarioBuilder::create();
    }

    /**
     * Migration helper: create equivalent TestScenarioBuilder from this legacy scenario.
     * This helps in migrating existing scenarios to the new builder pattern.
     */
    public function toTestScenarioBuilder(): TestScenarioBuilder
    {
        $builder = TestScenarioBuilder::create();

        // Extract name from definition if available
        $finalizedDef = $this->definition->finalize();
        $todo = $finalizedDef->currentInstance($this->when);
        
        if ($todo && $todo->name()) {
            $builder = $builder->withName($todo->name());
        }

        // Try to infer the schedule type from the definition
        // This is a best-effort conversion since we can't fully reverse-engineer
        // the original builder configuration from a Definition object
        
        return $builder;
    }
}
