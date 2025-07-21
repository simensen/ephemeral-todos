<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;

class CompletionAwareTest extends TestCase
{
    public function testDefaultCompletionAwarenessAppliesToAll()
    {
        $afterDueBy = AfterDueBy::oneDay();
        $afterExistingFor = AfterExistingFor::oneDay();

        $this->assertTrue($afterDueBy->appliesWhenComplete());
        $this->assertTrue($afterDueBy->appliesWhenIncomplete());
        $this->assertTrue($afterDueBy->appliesAlways());

        $this->assertTrue($afterExistingFor->appliesWhenComplete());
        $this->assertTrue($afterExistingFor->appliesWhenIncomplete());
        $this->assertTrue($afterExistingFor->appliesAlways());
    }

    public function testAndIsCompleteConfiguration()
    {
        $afterDueBy = AfterDueBy::oneDay()->andIsComplete();
        $afterExistingFor = AfterExistingFor::oneDay()->andIsComplete();

        $this->assertTrue($afterDueBy->appliesWhenComplete());
        $this->assertFalse($afterDueBy->appliesWhenIncomplete());
        $this->assertFalse($afterDueBy->appliesAlways());

        $this->assertTrue($afterExistingFor->appliesWhenComplete());
        $this->assertFalse($afterExistingFor->appliesWhenIncomplete());
        $this->assertFalse($afterExistingFor->appliesAlways());
    }

    public function testAndIsIncompleteConfiguration()
    {
        $afterDueBy = AfterDueBy::oneDay()->andIsIncomplete();
        $afterExistingFor = AfterExistingFor::oneDay()->andIsIncomplete();

        $this->assertFalse($afterDueBy->appliesWhenComplete());
        $this->assertTrue($afterDueBy->appliesWhenIncomplete());
        $this->assertFalse($afterDueBy->appliesAlways());

        $this->assertFalse($afterExistingFor->appliesWhenComplete());
        $this->assertTrue($afterExistingFor->appliesWhenIncomplete());
        $this->assertFalse($afterExistingFor->appliesAlways());
    }

    public function testWhetherCompletedOrNotConfiguration()
    {
        $afterDueBy = AfterDueBy::oneDay()->andIsComplete()->whetherCompletedOrNot();
        $afterExistingFor = AfterExistingFor::oneDay()->andIsIncomplete()->whetherCompletedOrNot();

        $this->assertTrue($afterDueBy->appliesWhenComplete());
        $this->assertTrue($afterDueBy->appliesWhenIncomplete());
        $this->assertTrue($afterDueBy->appliesAlways());

        $this->assertTrue($afterExistingFor->appliesWhenComplete());
        $this->assertTrue($afterExistingFor->appliesWhenIncomplete());
        $this->assertTrue($afterExistingFor->appliesAlways());
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
        $modified1 = $original->andIsComplete();
        $modified2 = $original->andIsIncomplete();
        $modified3 = $original->whetherCompletedOrNot();

        // All should be different instances
        $this->assertNotSame($original, $modified1);
        $this->assertNotSame($original, $modified2);
        $this->assertNotSame($original, $modified3);
        $this->assertNotSame($modified1, $modified2);
        $this->assertNotSame($modified1, $modified3);
        $this->assertNotSame($modified2, $modified3);

        // Original should remain unchanged
        $this->assertTrue($original->appliesWhenComplete());
        $this->assertTrue($original->appliesWhenIncomplete());
        $this->assertTrue($original->appliesAlways());
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
}
