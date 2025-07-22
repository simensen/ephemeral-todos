<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;
use Simensen\EphemeralTodos\Tests\Testing\AssertsCompletionAwareness;
use Simensen\EphemeralTodos\Tests\Testing\AssertsImmutability;
use Simensen\EphemeralTodos\Tests\Testing\TestScenarioBuilderHelpers;

class CompletionAwareTest extends TestCase
{
    use AssertsCompletionAwareness, AssertsImmutability, TestScenarioBuilderHelpers;
    public function testDefaultCompletionAwarenessAppliesToAll()
    {
        $afterDueBy = AfterDueBy::oneDay();
        $afterExistingFor = AfterExistingFor::oneDay();

        $this->assertHasCompletionAwareMethods($afterDueBy);
        $this->assertHasCompletionAwareMethods($afterExistingFor);
    }

    public function testAndIsCompleteConfiguration()
    {
        $afterDueBy = AfterDueBy::oneDay()->andIsComplete();
        $afterExistingFor = AfterExistingFor::oneDay()->andIsComplete();

        $this->assertCompletionAwarenessState($afterDueBy, true, false, false);
        $this->assertCompletionAwarenessState($afterExistingFor, true, false, false);
    }

    public function testAndIsIncompleteConfiguration()
    {
        $afterDueBy = AfterDueBy::oneDay()->andIsIncomplete();
        $afterExistingFor = AfterExistingFor::oneDay()->andIsIncomplete();

        $this->assertCompletionAwarenessState($afterDueBy, false, true, false);
        $this->assertCompletionAwarenessState($afterExistingFor, false, true, false);
    }

    public function testWhetherCompletedOrNotConfiguration()
    {
        $afterDueBy = AfterDueBy::oneDay()->andIsComplete()->whetherCompletedOrNot();
        $afterExistingFor = AfterExistingFor::oneDay()->andIsIncomplete()->whetherCompletedOrNot();

        $this->assertCompletionAwarenessState($afterDueBy, true, true, true);
        $this->assertCompletionAwarenessState($afterExistingFor, true, true, true);
    }

    public function testChainingCompletionAwareMethods()
    {
        // Start with default (applies to all)
        $original = AfterDueBy::oneDay();
        $this->assertTrue($original->appliesAlways());

        // Change to complete only
        $completeOnly = $original->andIsComplete();
        $this->assertTrue($completeOnly->appliesWhenComplete());
        $this->assertFalse($completeOnly->appliesWhenIncomplete());

        // Change to incomplete only
        $incompleteOnly = $completeOnly->andIsIncomplete();
        $this->assertFalse($incompleteOnly->appliesWhenComplete());
        $this->assertTrue($incompleteOnly->appliesWhenIncomplete());

        // Back to applying to all
        $backToAll = $incompleteOnly->whetherCompletedOrNot();
        $this->assertTrue($backToAll->appliesAlways());

        // Verify original is unchanged
        $this->assertTrue($original->appliesAlways());
    }

    public function testImmutabilityOfCompletionAwareMethods()
    {
        $original = AfterExistingFor::oneWeek();
        
        // Test immutability of completion awareness methods
        $this->assertCompletionAwarenessImmutability($original);
        
        // Verify original remains unchanged
        $this->assertCompletionAwarenessState($original, true, true, true);
    }

    public function testAppliesAlwaysLogic()
    {
        // appliesAlways should only be true when both complete and incomplete are true
        $both = AfterDueBy::oneDay();
        $this->assertTrue($both->appliesAlways());

        $completeOnly = $both->andIsComplete();
        $this->assertFalse($completeOnly->appliesAlways());

        $incompleteOnly = $both->andIsIncomplete();
        $this->assertFalse($incompleteOnly->appliesAlways());

        $backToBoth = $completeOnly->whetherCompletedOrNot();
        $this->assertTrue($backToBoth->appliesAlways());
    }

    /**
     * Demonstration of TestScenarioBuilder deletion rule configuration.
     * This showcases Phase 5: Deletion Rule Management functionality.
     */
    public function testTestScenarioBuilderDeletionRuleConfiguration()
    {
        // Demonstrate fluent deletion rule configuration using helper
        $scenario = $this->createDeletionTestScenario(
            'Completion Aware Todo',
            '1 day',
            '1 week',
            'incomplete'
        )->deleteAfterDue('1 day', 'complete'); // Override after due condition

        // Verify deletion rule properties are configured correctly using helper
        $this->assertDeletionRules(
            $scenario,
            '1 day',
            'complete',
            '1 week',
            'incomplete'
        );

        // Test utility methods using helpers
        $this->assertIntervalConversion($scenario, [
            '1 day' => 86400,
            '1 week' => 604800
        ]);

        $this->assertCompletionStateValidation($scenario, 
            ['complete', 'incomplete', 'either'],
            ['maybe']
        );
    }

    /**
     * Demonstration of deletion rule override behavior.
     */
    public function testDeletionRuleOverrideBehavior()
    {
        $scenario = $this->createBasicScenario('Override Test')
            ->deleteAfterDue('1 hour', 'complete')
            ->deleteAfterDue('1 day', 'incomplete'); // Should override previous

        // Last configuration should win - verify using helper
        $this->assertScenarioProperties($scenario, [
            'deleteAfterDueInterval' => '1 day',
            'deleteAfterDueCondition' => 'incomplete'
        ]);
    }
}
