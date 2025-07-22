<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Testing\Constraints;

use PHPUnit\Framework\Constraint\Constraint;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;
use Simensen\EphemeralTodos\Todo;

class TodoMatches extends Constraint
{
    private TestScenarioBuilder $scenario;

    public function __construct(TestScenarioBuilder $scenario)
    {
        $this->scenario = $scenario;
    }

    public function toString(): string
    {
        return 'matches the expected scenario';
    }

    protected function matches($other): bool
    {
        if (!$other instanceof Todo) {
            return false;
        }

        try {
            $this->scenario->assertTodoMatches($other);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function failureDescription($other): string
    {
        if (!$other instanceof Todo) {
            return 'Todo ' . $this->toString();
        }

        $description = "Todo {$this->toString()}\n";
        $description .= "Expected:\n";
        
        if ($this->scenario->getName() !== null) {
            $description .= "  - Name: '{$this->scenario->getName()}'\n";
        }
        
        if ($this->scenario->getPriority() !== null) {
            $description .= "  - Priority: '{$this->scenario->getPriority()}'\n";
        }
        
        if ($this->scenario->getScheduleTime() !== null) {
            $description .= "  - Schedule Time: '{$this->scenario->getScheduleTime()}'\n";
        }
        
        if ($this->scenario->getTimezone() !== null) {
            $description .= "  - Timezone: '{$this->scenario->getTimezone()}'\n";
        }

        $description .= "Actual:\n";
        $description .= "  - Name: '{$other->name()}'\n";
        $description .= "  - Priority: '{$other->priority()}'\n";
        
        if ($other->dueAt() !== null) {
            $description .= "  - Due Time: '{$other->dueAt()->format('H:i')}'\n";
            $description .= "  - Timezone: '{$other->dueAt()->getTimezone()->getName()}'\n";
        }

        return $description;
    }
}