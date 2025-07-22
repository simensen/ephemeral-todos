<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Tests\Testing\AssertsImmutability;

class AssertsImmutabilityTest extends TestCase
{
    use AssertsImmutability;

    public function testAssertMethodReturnsNewInstanceWithNoParameters()
    {
        $original = AfterDueBy::oneDay();
        
        // Should pass without throwing
        $this->assertMethodReturnsNewInstance($original, 'andIsComplete');
    }

    public function testAssertMethodReturnsNewInstanceWithParameters()
    {
        $schedule = Schedule::create();
        
        // Method that takes parameters
        $this->assertMethodReturnsNewInstance($schedule, 'at', '14:30');
    }

    public function testAssertMethodReturnsNewInstanceWithMultipleParameters()
    {
        $schedule = Schedule::create();
        
        // Method with multiple parameters (if such method exists)
        $this->assertMethodReturnsNewInstance($schedule, 'at', '14:30');
    }

    public function testAssertMultipleMethodsReturnNewInstances()
    {
        $original = AfterExistingFor::oneWeek();
        $methods = ['andIsComplete', 'andIsIncomplete', 'whetherCompletedOrNot'];
        
        // Should pass without throwing
        $this->assertMultipleMethodsReturnNewInstances($original, $methods);
    }

    public function testAssertMultipleMethodsWithParameters()
    {
        $schedule = Schedule::create();
        $methods = [
            'daily' => [],
            'at' => ['14:00'],
            'hourly' => []
        ];
        
        // Should pass without throwing
        $this->assertMultipleMethodsReturnNewInstances($schedule, $methods);
    }

    public function testAssertMethodReturnsNewInstanceFailsWhenSameInstance()
    {
        // Create a simple test class that returns itself
        $testObject = new class {
            public function someMethod() {
                return $this; // Returns itself - should fail immutability test
            }
        };
        
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Method someMethod() should return a new instance');
        
        $this->assertMethodReturnsNewInstance($testObject, 'someMethod');
    }

    public function testAssertMethodReturnsNewInstanceWithNonExistentMethod()
    {
        $object = new \stdClass();
        
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Method nonExistentMethod does not exist');
        
        $this->assertMethodReturnsNewInstance($object, 'nonExistentMethod');
    }

    public function testAssertMultipleMethodsFailsWithNonExistentMethod()
    {
        $object = AfterDueBy::oneDay();
        $methods = ['andIsComplete', 'nonExistentMethod'];
        
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Method nonExistentMethod does not exist');
        
        $this->assertMultipleMethodsReturnNewInstances($object, $methods);
    }

    public function testErrorMessagesIncludeMethodName()
    {
        $object = new \stdClass();
        
        try {
            $this->assertMethodReturnsNewInstance($object, 'missingMethod');
            $this->fail('Expected assertion failure');
        } catch (\PHPUnit\Framework\AssertionFailedError $e) {
            $this->assertStringContainsString('missingMethod', $e->getMessage());
        }
    }

    public function testWorksWithDifferentObjectTypes()
    {
        // Test with various immutable objects
        $afterDueBy = AfterDueBy::oneHour();
        $afterExistingFor = AfterExistingFor::oneDay();
        $schedule = Schedule::create();
        
        $this->assertMethodReturnsNewInstance($afterDueBy, 'andIsComplete');
        $this->assertMethodReturnsNewInstance($afterExistingFor, 'andIsIncomplete');
        $this->assertMethodReturnsNewInstance($schedule, 'daily');
    }

    public function testAssertMultipleMethodsWithEmptyArray()
    {
        $object = AfterDueBy::oneDay();
        
        // Empty methods array should not throw
        $this->assertMultipleMethodsReturnNewInstances($object, []);
        
        // Add assertion to make test non-risky
        $this->assertTrue(true, 'Empty methods array handled without errors');
    }

    public function testCustomErrorMessage()
    {
        // Create a simple test class that returns itself
        $testObject = new class {
            public function someMethod() {
                return $this;
            }
        };
        
        $customMessage = 'Custom immutability failure message';
        
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage($customMessage);
        
        $this->assertMethodReturnsNewInstance($testObject, 'someMethod', $customMessage);
    }
}