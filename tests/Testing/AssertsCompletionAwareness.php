<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Testing;

use PHPUnit\Framework\Assert;

/**
 * Trait for testing completion awareness behavior in objects.
 * 
 * This trait provides standardized assertion methods for testing objects that
 * implement completion awareness patterns with methods like appliesWhenComplete(),
 * appliesWhenIncomplete(), appliesAlways(), and configuration methods.
 * 
 * Usage:
 *   class MyTest extends TestCase
 *   {
 *       use AssertsCompletionAwareness;
 *       
 *       public function testMyCompletionAwareObject()
 *       {
 *           $object = MyClass::create();
 *           $this->assertHasCompletionAwareMethods($object);
 *           $this->assertCanConfigureCompletionAwareness($object);
 *       }
 *   }
 */
trait AssertsCompletionAwareness
{
    /**
     * Assert that an object has the basic completion awareness methods.
     * 
     * @param mixed $object The object to test
     * @param string $message Optional custom failure message
     */
    protected function assertHasCompletionAwareMethods($object, string $message = ''): void
    {
        $requiredMethods = ['appliesWhenComplete', 'appliesWhenIncomplete', 'appliesAlways'];
        
        foreach ($requiredMethods as $method) {
            if (!method_exists($object, $method)) {
                $defaultMessage = "Object does not implement completion awareness methods. Missing method: {$method}. Expected methods: " . implode(', ', $requiredMethods);
                Assert::fail($message ?: $defaultMessage);
            }
        }
        
        // Test that the default state applies to all completion states
        Assert::assertTrue($object->appliesWhenComplete(), $message ?: 'Default completion awareness should apply when complete');
        Assert::assertTrue($object->appliesWhenIncomplete(), $message ?: 'Default completion awareness should apply when incomplete');  
        Assert::assertTrue($object->appliesAlways(), $message ?: 'Default completion awareness should apply always');
    }

    /**
     * Assert that an object can be configured for completion awareness.
     * 
     * @param mixed $object The object to test
     * @param string $message Optional custom failure message
     */
    protected function assertCanConfigureCompletionAwareness($object, string $message = ''): void
    {
        $configMethods = ['andIsComplete', 'andIsIncomplete', 'whetherCompletedOrNot'];
        
        foreach ($configMethods as $method) {
            if (!method_exists($object, $method)) {
                $defaultMessage = "Object does not support completion awareness configuration. Missing method: {$method}. Expected methods: " . implode(', ', $configMethods);
                Assert::fail($message ?: $defaultMessage);
            }
        }

        // Test complete-only configuration
        $completeOnly = $object->andIsComplete();
        Assert::assertTrue($completeOnly->appliesWhenComplete(), $message ?: 'andIsComplete() should apply when complete');
        Assert::assertFalse($completeOnly->appliesWhenIncomplete(), $message ?: 'andIsComplete() should not apply when incomplete');
        Assert::assertFalse($completeOnly->appliesAlways(), $message ?: 'andIsComplete() should not apply always');

        // Test incomplete-only configuration  
        $incompleteOnly = $object->andIsIncomplete();
        Assert::assertFalse($incompleteOnly->appliesWhenComplete(), $message ?: 'andIsIncomplete() should not apply when complete');
        Assert::assertTrue($incompleteOnly->appliesWhenIncomplete(), $message ?: 'andIsIncomplete() should apply when incomplete');
        Assert::assertFalse($incompleteOnly->appliesAlways(), $message ?: 'andIsIncomplete() should not apply always');

        // Test whether-completed-or-not configuration
        $whetherCompletedOrNot = $object->whetherCompletedOrNot();
        Assert::assertTrue($whetherCompletedOrNot->appliesWhenComplete(), $message ?: 'whetherCompletedOrNot() should apply when complete');
        Assert::assertTrue($whetherCompletedOrNot->appliesWhenIncomplete(), $message ?: 'whetherCompletedOrNot() should apply when incomplete');
        Assert::assertTrue($whetherCompletedOrNot->appliesAlways(), $message ?: 'whetherCompletedOrNot() should apply always');
    }

    /**
     * Assert that an object has specific completion awareness states.
     * 
     * @param mixed $object The object to test
     * @param bool $expectsComplete Whether object should apply when complete
     * @param bool $expectsIncomplete Whether object should apply when incomplete
     * @param bool $expectsAlways Whether object should apply always
     * @param string $message Optional custom failure message
     */
    protected function assertCompletionAwarenessState(
        $object, 
        bool $expectsComplete, 
        bool $expectsIncomplete, 
        bool $expectsAlways,
        string $message = ''
    ): void {
        $prefix = $message ? $message . ' - ' : '';
        
        Assert::assertEquals(
            $expectsComplete, 
            $object->appliesWhenComplete(),
            $prefix . "Expected appliesWhenComplete() to be " . ($expectsComplete ? 'true' : 'false')
        );
        
        Assert::assertEquals(
            $expectsIncomplete, 
            $object->appliesWhenIncomplete(),
            $prefix . "Expected appliesWhenIncomplete() to be " . ($expectsIncomplete ? 'true' : 'false')
        );
        
        Assert::assertEquals(
            $expectsAlways, 
            $object->appliesAlways(),
            $prefix . "Expected appliesAlways() to be " . ($expectsAlways ? 'true' : 'false')
        );
    }

    /**
     * Assert that completion awareness configuration methods return new instances.
     * 
     * @param mixed $object The object to test
     * @param string $message Optional custom failure message
     */
    protected function assertCompletionAwarenessImmutability($object, string $message = ''): void
    {
        $prefix = $message ? $message . ' - ' : '';
        
        $complete = $object->andIsComplete();
        $incomplete = $object->andIsIncomplete(); 
        $whetherCompletedOrNot = $object->whetherCompletedOrNot();

        Assert::assertNotSame($object, $complete, $prefix . 'andIsComplete() should return new instance');
        Assert::assertNotSame($object, $incomplete, $prefix . 'andIsIncomplete() should return new instance');
        Assert::assertNotSame($object, $whetherCompletedOrNot, $prefix . 'whetherCompletedOrNot() should return new instance');
        Assert::assertNotSame($complete, $incomplete, $prefix . 'Configuration methods should return different instances');
    }
}