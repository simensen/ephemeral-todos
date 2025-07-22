<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\AssertionFailedError;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;
use Simensen\EphemeralTodos\Testing\Constraints\TodoMatches;
use Simensen\EphemeralTodos\Testing\Constraints\DefinitionMatches;

class AssertionMethodsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2025-01-19 12:00:00 UTC');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function testAssertTodoMatchesMethod()
    {
        $scenario = TestScenarioBuilder::dailyMeeting()
            ->withName('Test Meeting');

        $definition = $scenario->buildDefinition();
        $finalizedDefinition = $definition->finalize();
        $todo = $finalizedDefinition->currentInstance(Carbon::parse('2025-01-19 09:00:00'));

        // This should pass without throwing
        $scenario->assertTodoMatches($todo);

        $this->assertTrue(true); // Test passes if no exception thrown
    }

    public function testAssertTodoMatchesFailsOnWrongName()
    {
        $scenario = TestScenarioBuilder::dailyMeeting()
            ->withName('Expected Meeting');

        $wrongScenario = TestScenarioBuilder::dailyMeeting()
            ->withName('Wrong Meeting');

        $definition = $wrongScenario->buildDefinition();
        $finalizedDefinition = $definition->finalize();
        $todo = $finalizedDefinition->currentInstance(Carbon::parse('2025-01-19 09:00:00'));

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Expected Meeting');

        $scenario->assertTodoMatches($todo);
    }

    public function testAssertTodoMatchesFailsOnWrongPriority()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Test Todo')
            ->withPriority('high')
            ->daily()
            ->at('10:00'); // Expects priority 4

        $wrongScenario = TestScenarioBuilder::create()
            ->withName('Test Todo')
            ->withPriority('low')
            ->daily()
            ->at('10:00'); // Creates priority 2

        $definition = $wrongScenario->buildDefinition();
        $finalizedDefinition = $definition->finalize();
        $todo = $finalizedDefinition->currentInstance(Carbon::parse('2025-01-19 10:00:00'));

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('priority');

        $scenario->assertTodoMatches($todo);
    }

    public function testAssertDefinitionMatchesMethod()
    {
        $scenario = TestScenarioBuilder::weeklyReview()
            ->withName('Test Review');

        $definition = $scenario->buildDefinition();

        // This should pass without throwing
        $scenario->assertDefinitionMatches($definition);

        $this->assertTrue(true); // Test passes if no exception thrown
    }

    public function testTodoMatchesConstraint()
    {
        $scenario = TestScenarioBuilder::dailyMeeting()
            ->withName('Meeting Test');

        $definition = $scenario->buildDefinition();
        $finalizedDefinition = $definition->finalize();
        $todo = $finalizedDefinition->currentInstance(Carbon::parse('2025-01-19 09:00:00'));

        $constraint = new TodoMatches($scenario);

        $this->assertThat($todo, $constraint);
    }

    public function testDefinitionMatchesConstraint()
    {
        $scenario = TestScenarioBuilder::quickReminder()
            ->withName('Reminder Test');

        $definition = $scenario->buildDefinition();

        $constraint = new DefinitionMatches($scenario);

        $this->assertThat($definition, $constraint);
    }

    public function testAssertTodoMatchesWithTimezoneValidation()
    {
        $this->markTestIncomplete('Timezone handling in scenarios will be enhanced in future phase');

        // $scenario = TestScenarioBuilder::create()
        //     ->withName('Timezone Test')
        //     ->withTimezone('America/New_York')
        //     ->daily()
        //     ->at('14:00');
        // 
        // $definition = $scenario->buildDefinition();
        // $finalizedDefinition = $definition->finalize();
        // 
        // // Test at 2 PM in UTC (should match 2 PM schedule)
        // $todo = $finalizedDefinition->currentInstance(Carbon::parse('2025-01-19 14:00:00'));
        // 
        // $scenario->assertTodoMatches($todo);
    }

    public function testAssertTodoMatchesWithDeletionRules()
    {
        $this->markTestIncomplete('Deletion rule assertion will be implemented in Phase 5');

        // $scenario = TestScenarioBuilder::dailyMeeting()
        //     ->withName('Deletion Test')
        //     ->deleteAfterDue('1 day', 'complete');
        // 
        // $definition = $scenario->buildDefinition();
        // $finalizedDefinition = $definition->finalize();
        // $todo = $finalizedDefinition->currentInstance(Carbon::parse('2025-01-19 09:00:00'));
        // 
        // $scenario->assertTodoMatches($todo);
    }

    public function testDeepTodoValidationChecksAllProperties()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Complete Test')
            ->withPriority('medium')
            ->daily()
            ->at('10:30');

        $definition = $scenario->buildDefinition();
        $finalizedDefinition = $definition->finalize();
        $todo = $finalizedDefinition->currentInstance(Carbon::parse('2025-01-19 10:30:00'));

        // Should validate name, priority, due time, and schedule type
        $scenario->assertTodoMatches($todo);

        // Verify individual properties
        $this->assertEquals('Complete Test', $todo->name());
        $this->assertEquals(3, $todo->priority()); // Medium priority
        $this->assertEquals(Carbon::parse('2025-01-19 10:30:00'), $todo->dueAt());
    }

    public function testAssertionMethodsHandleNullTodos()
    {
        $scenario = TestScenarioBuilder::dailyMeeting();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('null');

        $scenario->assertTodoMatches(null);
    }

    public function testConstraintProvidesDetailedFailureMessages()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Expected Name')
            ->withPriority('high')
            ->daily()
            ->at('10:00');

        $wrongDefinition = TestScenarioBuilder::create()
            ->withName('Wrong Name')
            ->withPriority('low')
            ->daily()
            ->at('10:00')
            ->buildDefinition();

        $constraint = new DefinitionMatches($scenario);

        try {
            $this->assertThat($wrongDefinition, $constraint);
            $this->fail('Expected assertion to fail');
        } catch (AssertionFailedError $e) {
            $this->assertStringContainsString('Expected Name', $e->getMessage());
            // The constraint message shows the expected scenario, not the actual todo name
            $this->assertStringContainsString('Definition', $e->getMessage());
        }
    }
}