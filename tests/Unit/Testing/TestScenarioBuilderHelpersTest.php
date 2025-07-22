<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;
use Simensen\EphemeralTodos\Tests\Testing\TestScenarioBuilderHelpers;

class TestScenarioBuilderHelpersTest extends TestCase
{
    use TestScenarioBuilderHelpers;

    public function testCreateBasicScenarioWithDefaults(): void
    {
        $scenario = $this->createBasicScenario('Test Todo');
        
        $this->assertInstanceOf(TestScenarioBuilder::class, $scenario);
        $this->assertScenarioCanBuild($scenario);
    }

    public function testCreateBasicScenarioWithOverrides(): void
    {
        $scenario = $this->createBasicScenario('Test Todo', [
            'schedule' => 'weekly',
            'weekDay' => 'monday',
            'time' => '15:30'
        ]);
        
        $this->assertInstanceOf(TestScenarioBuilder::class, $scenario);
        $this->assertScenarioCanBuild($scenario);
    }

    public function testCreateCompletionAwareScenario(): void
    {
        $scenario = $this->createCompletionAwareScenario('Completion Test', 'complete');
        
        $this->assertInstanceOf(TestScenarioBuilder::class, $scenario);
        $this->assertScenarioProperties($scenario, [
            'deleteAfterDueCondition' => 'complete',
            'deleteAfterExistingCondition' => 'complete'
        ]);
    }

    public function testCreateDeletionTestScenario(): void
    {
        $scenario = $this->createDeletionTestScenario(
            'Deletion Test',
            '2 hours',
            '3 days', 
            'incomplete'
        );
        
        $this->assertDeletionRules(
            $scenario,
            '2 hours',
            'incomplete',
            '3 days',
            'incomplete'
        );
    }

    public function testAssertCompletionStateValidation(): void
    {
        $scenario = TestScenarioBuilder::create();
        
        $this->assertCompletionStateValidation(
            $scenario,
            ['complete', 'incomplete', 'either'],
            ['maybe', 'never']
        );
    }

    public function testAssertIntervalConversion(): void
    {
        $scenario = TestScenarioBuilder::create();
        
        $this->assertIntervalConversion($scenario, [
            '1 hour' => 3600,
            '1 day' => 86400,
            '1 week' => 604800,
            '2 weeks' => 1209600
        ]);
    }

    public function testScenarioPropertiesAssertion(): void
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Property Test')
            ->deleteAfterDue('1 day', 'complete')
            ->deleteAfterExisting('1 week', 'incomplete');
        
        $this->assertScenarioProperties($scenario, [
            'deleteAfterDueInterval' => '1 day',
            'deleteAfterDueCondition' => 'complete',
            'deleteAfterExistingInterval' => '1 week',
            'deleteAfterExistingCondition' => 'incomplete'
        ]);
    }

    public function testDeletionRulesAssertion(): void
    {
        $scenario = TestScenarioBuilder::create()
            ->deleteAfterDue('30 minutes', 'either')
            ->deleteAfterExisting('2 weeks', 'complete');
            
        $this->assertDeletionRules(
            $scenario,
            '30 minutes',
            'either',
            '2 weeks',
            'complete'
        );
    }
}