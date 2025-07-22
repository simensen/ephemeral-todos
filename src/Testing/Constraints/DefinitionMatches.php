<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Testing\Constraints;

use PHPUnit\Framework\Constraint\Constraint;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;
use Simensen\EphemeralTodos\Definition;

class DefinitionMatches extends Constraint
{
    private TestScenarioBuilder $scenario;

    public function __construct(TestScenarioBuilder $scenario)
    {
        $this->scenario = $scenario;
    }

    public function toString(): string
    {
        return 'matches the expected scenario definition';
    }

    protected function matches($other): bool
    {
        if (!$other instanceof Definition) {
            return false;
        }

        try {
            $this->scenario->assertDefinitionMatches($other);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function failureDescription($other): string
    {
        if (!$other instanceof Definition) {
            return 'Definition ' . $this->toString();
        }

        $description = "Definition {$this->toString()}\n";
        $description .= "Expected scenario:\n";
        
        if ($this->scenario->getName() !== null) {
            $description .= "  - Name: '{$this->scenario->getName()}'\n";
        }
        
        if ($this->scenario->getPriority() !== null) {
            $description .= "  - Priority: '{$this->scenario->getPriority()}'\n";
        }
        
        if ($this->scenario->getScheduleType() !== null) {
            $description .= "  - Schedule Type: '{$this->scenario->getScheduleType()}'\n";
        }
        
        if ($this->scenario->getScheduleTime() !== null) {
            $description .= "  - Schedule Time: '{$this->scenario->getScheduleTime()}'\n";
        }

        $description .= "Actual definition produces todos that don't match these expectations.";

        return $description;
    }
}