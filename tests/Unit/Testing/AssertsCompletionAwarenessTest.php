<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\Tests\Testing\AssertsCompletionAwareness;

class AssertsCompletionAwarenessTest extends TestCase
{
    use AssertsCompletionAwareness;

    public function testAssertHasCompletionAwareMethodsWithValidObject()
    {
        $object = AfterDueBy::oneDay();
        
        // Should pass without throwing
        $this->assertHasCompletionAwareMethods($object);
    }

    public function testAssertHasCompletionAwareMethodsWithAfterExistingFor()
    {
        $object = AfterExistingFor::oneWeek();
        
        // Should pass without throwing
        $this->assertHasCompletionAwareMethods($object);
    }

    public function testAssertCanConfigureCompletionAwarenessWithAfterDueBy()
    {
        $object = AfterDueBy::oneDay();
        
        // Should pass without throwing
        $this->assertCanConfigureCompletionAwareness($object);
    }

    public function testAssertCanConfigureCompletionAwarenessWithAfterExistingFor()
    {
        $object = AfterExistingFor::oneWeek();
        
        // Should pass without throwing
        $this->assertCanConfigureCompletionAwareness($object);
    }

    public function testAssertHasCompletionAwareMethodsWithInvalidObject()
    {
        $invalidObject = new \stdClass();
        
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Object does not implement completion awareness methods');
        
        $this->assertHasCompletionAwareMethods($invalidObject);
    }

    public function testAssertCanConfigureCompletionAwarenessWithInvalidObject()
    {
        $invalidObject = new \stdClass();
        
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Object does not support completion awareness configuration');
        
        $this->assertCanConfigureCompletionAwareness($invalidObject);
    }

    public function testAssertCompletionAwarenessStateWithCompleteOnly()
    {
        $completeOnly = AfterDueBy::oneDay()->andIsComplete();
        
        $this->assertCompletionAwarenessState($completeOnly, true, false, false);
    }

    public function testAssertCompletionAwarenessStateWithIncompleteOnly()
    {
        $incompleteOnly = AfterExistingFor::oneDay()->andIsIncomplete();
        
        $this->assertCompletionAwarenessState($incompleteOnly, false, true, false);
    }

    public function testAssertCompletionAwarenessStateWithBoth()
    {
        $both = AfterDueBy::oneDay()->whetherCompletedOrNot();
        
        $this->assertCompletionAwarenessState($both, true, true, true);
    }

    public function testAssertCompletionAwarenessStateWithFailure()
    {
        $completeOnly = AfterDueBy::oneDay()->andIsComplete();
        
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        
        // This should fail because completeOnly doesn't apply when incomplete
        $this->assertCompletionAwarenessState($completeOnly, true, true, true);
    }

    public function testTraitWorksWithDifferentCompletionAwareTypes()
    {
        // Test that trait methods work with different completion-aware objects
        $afterDueBy = AfterDueBy::twoHours();
        $afterExistingFor = AfterExistingFor::threeDays();
        
        $this->assertHasCompletionAwareMethods($afterDueBy);
        $this->assertHasCompletionAwareMethods($afterExistingFor);
        
        $this->assertCanConfigureCompletionAwareness($afterDueBy);
        $this->assertCanConfigureCompletionAwareness($afterExistingFor);
    }

    public function testErrorMessagesAreDescriptive()
    {
        $invalidObject = new \stdClass();
        
        try {
            $this->assertHasCompletionAwareMethods($invalidObject);
            $this->fail('Expected assertion failure');
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            $this->assertStringContainsString('completion awareness methods', $e->getMessage());
            $this->assertStringContainsString('appliesWhenComplete', $e->getMessage());
        }
        
        try {
            $this->assertCanConfigureCompletionAwareness($invalidObject);
            $this->fail('Expected assertion failure');
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            $this->assertStringContainsString('completion awareness configuration', $e->getMessage());
            $this->assertStringContainsString('andIsComplete', $e->getMessage());
        }
    }
}