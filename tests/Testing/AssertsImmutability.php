<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Testing;

use PHPUnit\Framework\Assert;

/**
 * Trait for testing immutability behavior in objects.
 * 
 * This trait provides standardized assertion methods for testing objects that
 * implement immutable patterns where method calls return new instances rather
 * than modifying the original object.
 * 
 * Usage:
 *   class MyTest extends TestCase
 *   {
 *       use AssertsImmutability;
 *       
 *       public function testMyImmutableObject()
 *       {
 *           $object = MyClass::create();
 *           $this->assertMethodReturnsNewInstance($object, 'withSomeValue', 'parameter');
 *           $this->assertMultipleMethodsReturnNewInstances($object, ['method1', 'method2']);
 *       }
 *   }
 */
trait AssertsImmutability
{
    /**
     * Assert that a method call returns a new instance rather than modifying the original.
     * 
     * @param mixed $object The object to test
     * @param string $methodName The method to call
     * @param mixed ...$args Arguments to pass to the method (last string argument is treated as custom message)
     */
    protected function assertMethodReturnsNewInstance($object, string $methodName, ...$args): void
    {
        // Extract custom message if provided as last argument and it's a string that looks like a message
        $message = '';
        if (count($args) > 0 && is_string(end($args))) {
            $lastArg = end($args);
            // Only treat as message if it looks like a descriptive message (contains common message words)
            if (preg_match('/\b(should|expected|test|message|error|fail)\b/i', $lastArg)) {
                $message = array_pop($args);
            }
        }
        
        if (!method_exists($object, $methodName)) {
            $defaultMessage = "Method {$methodName} does not exist on " . get_class($object);
            Assert::fail($message ?: $defaultMessage);
        }

        $result = call_user_func_array([$object, $methodName], $args);
        
        $defaultMessage = "Method {$methodName}() should return a new instance, but returned the same object";
        Assert::assertNotSame($object, $result, $message ?: $defaultMessage);
    }

    /**
     * Assert that multiple methods return new instances.
     * 
     * @param mixed $object The object to test
     * @param array $methods Array of method names or method name => arguments pairs
     * @param string $message Optional custom failure message
     */
    protected function assertMultipleMethodsReturnNewInstances($object, array $methods, string $message = ''): void
    {
        foreach ($methods as $methodName => $args) {
            // Handle both indexed and associative arrays
            if (is_numeric($methodName)) {
                // Indexed array: ['method1', 'method2']
                $methodName = $args;
                $args = [];
            }
            // Associative array: ['method1' => ['arg1', 'arg2'], 'method2' => []]
            
            if (!is_array($args)) {
                $args = [];
            }

            if (!method_exists($object, $methodName)) {
                $defaultMessage = "Method {$methodName} does not exist on " . get_class($object);
                Assert::fail($message ?: $defaultMessage);
            }

            $result = call_user_func_array([$object, $methodName], $args);
            
            $prefix = $message ? $message . ' - ' : '';
            $defaultMessage = $prefix . "Method {$methodName}() should return a new instance, but returned the same object";
            Assert::assertNotSame($object, $result, $defaultMessage);
        }
    }

    /**
     * Assert that an object maintains immutability across a chain of method calls.
     * 
     * @param mixed $object The object to test
     * @param array $methodChain Array of method calls to chain
     * @param string $message Optional custom failure message
     */
    protected function assertImmutableChain($object, array $methodChain, string $message = ''): void
    {
        $current = $object;
        $instances = [$current];
        
        foreach ($methodChain as $methodCall) {
            if (is_string($methodCall)) {
                $methodName = $methodCall;
                $args = [];
            } else {
                $methodName = $methodCall['method'];
                $args = $methodCall['args'] ?? [];
            }
            
            if (!method_exists($current, $methodName)) {
                $defaultMessage = "Method {$methodName} does not exist on " . get_class($current);
                Assert::fail($message ?: $defaultMessage);
            }
            
            $next = call_user_func_array([$current, $methodName], $args);
            
            // Check that new instance was returned
            $prefix = $message ? $message . ' - ' : '';
            $defaultMessage = $prefix . "Method {$methodName}() should return a new instance";
            Assert::assertNotSame($current, $next, $defaultMessage);
            
            // Check that new instance is different from all previous instances
            foreach ($instances as $index => $instance) {
                $defaultMessage = $prefix . "Method {$methodName}() should return a unique instance (differs from instance {$index})";
                Assert::assertNotSame($instance, $next, $defaultMessage);
            }
            
            $instances[] = $next;
            $current = $next;
        }
        
        // Verify original object is unchanged by calling a method on it
        // This assumes the object has at least one method that can be called for verification
        if (method_exists($object, 'appliesWhenComplete')) {
            // Test completion-aware objects
            $originalResult = $object->appliesWhenComplete();
            Assert::assertIsBool($originalResult, 'Original object should remain functional after method chain');
        }
    }
}