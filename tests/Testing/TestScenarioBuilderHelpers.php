<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Testing;

use PHPUnit\Framework\Assert;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;

/**
 * Trait for common TestScenarioBuilder patterns and assertions.
 * 
 * This trait provides helper methods for common TestScenarioBuilder usage patterns,
 * reducing code duplication when testing scenario-based functionality.
 * 
 * Usage:
 *   class MyTest extends TestCase
 *   {
 *       use TestScenarioBuilderHelpers;
 *       
 *       public function testScenario()
 *       {
 *           $scenario = $this->createBasicScenario('Test Todo');
 *           $this->assertScenarioProperties($scenario, [
 *               'name' => 'Test Todo',
 *               'schedule' => 'daily'
 *           ]);
 *       }
 *   }
 */
trait TestScenarioBuilderHelpers
{
    /**
     * Create a basic scenario with common default settings.
     * 
     * @param string $name The name for the todo scenario
     * @param array $overrides Optional overrides for default settings
     * @return TestScenarioBuilder The configured scenario builder
     */
    protected function createBasicScenario(string $name, array $overrides = []): TestScenarioBuilder
    {
        $scenario = TestScenarioBuilder::create()->withName($name);
        
        // Apply default settings
        $defaults = [
            'schedule' => 'daily',
            'time' => '09:00',
            'priority' => 'medium'
        ];
        
        $settings = array_merge($defaults, $overrides);
        
        // Apply schedule
        if (isset($settings['schedule'])) {
            switch ($settings['schedule']) {
                case 'daily':
                    $scenario = $scenario->daily();
                    break;
                case 'weekly':
                    $weekDay = $settings['weekDay'] ?? 'sunday';
                    $scenario = $scenario->weekly($weekDay);
                    break;
            }
        }
        
        // Apply time if provided
        if (isset($settings['time'])) {
            $scenario = $scenario->at($settings['time']);
        }
        
        // Apply priority if provided
        if (isset($settings['priority'])) {
            $scenario = $scenario->withPriority($settings['priority']);
        }
        
        return $scenario;
    }

    /**
     * Create a scenario configured for completion awareness testing.
     * 
     * @param string $name The scenario name
     * @param string $deletionCondition Either 'complete', 'incomplete', or 'either'
     * @return TestScenarioBuilder Configured scenario
     */
    protected function createCompletionAwareScenario(string $name, string $deletionCondition = 'either'): TestScenarioBuilder
    {
        $scenario = $this->createBasicScenario($name)
            ->deleteAfterDue('1 day', $deletionCondition)
            ->deleteAfterExisting('1 week', $deletionCondition);
            
        return $scenario;
    }

    /**
     * Create a scenario with deletion rules for testing.
     * 
     * @param string $name The scenario name
     * @param string $afterDueInterval Interval after due date (e.g., '1 day')
     * @param string $afterExistingInterval Interval after creation (e.g., '1 week')
     * @param string $condition Completion condition ('complete', 'incomplete', 'either')
     * @return TestScenarioBuilder Configured scenario
     */
    protected function createDeletionTestScenario(
        string $name, 
        string $afterDueInterval = '1 day', 
        string $afterExistingInterval = '1 week',
        string $condition = 'either'
    ): TestScenarioBuilder {
        return $this->createBasicScenario($name)
            ->deleteAfterDue($afterDueInterval, $condition)
            ->deleteAfterExisting($afterExistingInterval, $condition);
    }

    /**
     * Assert that a scenario has expected property values.
     * 
     * @param TestScenarioBuilder $scenario The scenario to check
     * @param array $expectedProperties Associative array of property => expected_value
     * @param string $message Optional custom failure message
     */
    protected function assertScenarioProperties(TestScenarioBuilder $scenario, array $expectedProperties, string $message = ''): void
    {
        foreach ($expectedProperties as $property => $expectedValue) {
            $getterMethod = 'get' . ucfirst($property);
            
            if (method_exists($scenario, $getterMethod)) {
                $actualValue = $scenario->{$getterMethod}();
                $propertyMessage = $message ? $message . " - " : "";
                $propertyMessage .= "Scenario property '{$property}' should equal '{$expectedValue}'";
                
                Assert::assertEquals($expectedValue, $actualValue, $propertyMessage);
            } else {
                Assert::fail($message ?: "Scenario does not have getter method for property '{$property}'");
            }
        }
    }

    /**
     * Assert that a scenario can be built without errors.
     * 
     * @param TestScenarioBuilder $scenario The scenario to validate
     * @param string $message Optional custom failure message
     */
    protected function assertScenarioCanBuild(TestScenarioBuilder $scenario, string $message = ''): void
    {
        try {
            $definition = $scenario->buildDefinition();
            $finalized = $definition->finalize();
            Assert::assertNotNull($finalized, $message ?: "Scenario should be able to build and finalize");
        } catch (\Exception $e) {
            Assert::fail($message ?: "Scenario should build without errors, but got: " . $e->getMessage());
        }
    }

    /**
     * Assert that scenario deletion rules are configured correctly.
     * 
     * @param TestScenarioBuilder $scenario The scenario to check
     * @param string $expectedAfterDueInterval Expected interval after due date
     * @param string $expectedAfterDueCondition Expected completion condition for after due
     * @param string $expectedAfterExistingInterval Expected interval after existing
     * @param string $expectedAfterExistingCondition Expected completion condition for after existing
     * @param string $message Optional custom failure message
     */
    protected function assertDeletionRules(
        TestScenarioBuilder $scenario,
        string $expectedAfterDueInterval,
        string $expectedAfterDueCondition,
        string $expectedAfterExistingInterval,
        string $expectedAfterExistingCondition,
        string $message = ''
    ): void {
        $this->assertScenarioProperties($scenario, [
            'deleteAfterDueInterval' => $expectedAfterDueInterval,
            'deleteAfterDueCondition' => $expectedAfterDueCondition,
            'deleteAfterExistingInterval' => $expectedAfterExistingInterval,
            'deleteAfterExistingCondition' => $expectedAfterExistingCondition,
        ], $message);
    }

    /**
     * Assert that scenario completion validation works correctly.
     * 
     * @param TestScenarioBuilder $scenario The scenario to test
     * @param array $validStates Array of states that should be valid
     * @param array $invalidStates Array of states that should be invalid
     * @param string $message Optional custom failure message
     */
    protected function assertCompletionStateValidation(
        TestScenarioBuilder $scenario,
        array $validStates = ['complete', 'incomplete', 'either'],
        array $invalidStates = ['maybe', 'never', 'always'],
        string $message = ''
    ): void {
        foreach ($validStates as $state) {
            Assert::assertTrue($scenario->isValidCompletionState($state), 
                $message ?: "State '{$state}' should be valid");
        }
        
        foreach ($invalidStates as $state) {
            Assert::assertFalse($scenario->isValidCompletionState($state),
                $message ?: "State '{$state}' should be invalid");
        }
    }

    /**
     * Assert that scenario time conversion utilities work correctly.
     * 
     * @param TestScenarioBuilder $scenario The scenario to test
     * @param array $intervalTests Array of interval => expected_seconds pairs
     * @param string $message Optional custom failure message
     */
    protected function assertIntervalConversion(
        TestScenarioBuilder $scenario,
        array $intervalTests = ['1 day' => 86400, '1 week' => 604800, '1 hour' => 3600],
        string $message = ''
    ): void {
        foreach ($intervalTests as $interval => $expectedSeconds) {
            $actualSeconds = $scenario->convertIntervalToSeconds($interval);
            Assert::assertEquals($expectedSeconds, $actualSeconds,
                $message ?: "Interval '{$interval}' should convert to {$expectedSeconds} seconds");
        }
    }
}