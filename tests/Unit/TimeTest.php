<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use Simensen\EphemeralTodos\Tests\TestCase;
use Simensen\EphemeralTodos\Time;

class TimeTest extends TestCase
{
    public function test_constructor_accepts_seconds(): void
    {
        $time = new Time(3600); // 1 hour
        
        $this->assertEquals(3600, $time->inSeconds());
    }

    public function test_constructor_accepts_zero_seconds(): void
    {
        $time = new Time(0);
        
        $this->assertEquals(0, $time->inSeconds());
    }

    public function test_constructor_accepts_negative_seconds(): void
    {
        $time = new Time(-1800); // -30 minutes
        
        $this->assertEquals(-1800, $time->inSeconds());
    }

    public function test_in_seconds_returns_constructor_value(): void
    {
        $seconds = 7200; // 2 hours
        $time = new Time($seconds);
        
        $this->assertEquals($seconds, $time->inSeconds());
    }

    public function test_invert_returns_new_instance(): void
    {
        $original = new Time(1800); // 30 minutes
        $inverted = $original->invert();
        
        $this->assertNotSame($original, $inverted);
        $this->assertEquals(1800, $original->inSeconds());
        $this->assertEquals(-1800, $inverted->inSeconds());
    }

    public function test_invert_positive_to_negative(): void
    {
        $time = new Time(3600);
        $inverted = $time->invert();
        
        $this->assertEquals(3600, $time->inSeconds());
        $this->assertEquals(-3600, $inverted->inSeconds());
    }

    public function test_invert_negative_to_positive(): void
    {
        $time = new Time(-3600);
        $inverted = $time->invert();
        
        $this->assertEquals(-3600, $time->inSeconds());
        $this->assertEquals(3600, $inverted->inSeconds());
    }

    public function test_invert_zero_stays_zero(): void
    {
        $time = new Time(0);
        $inverted = $time->invert();
        
        $this->assertEquals(0, $time->inSeconds());
        $this->assertEquals(0, $inverted->inSeconds());
    }

    public function test_time_is_immutable(): void
    {
        $time = new Time(1800);
        
        // Verify all methods return new instances or values
        $inverted = $time->invert();
        $this->assertNotSame($time, $inverted);
        
        // Verify original is unchanged
        $this->assertEquals(1800, $time->inSeconds());
    }

    public function test_multiple_inverts(): void
    {
        $original = new Time(2700); // 45 minutes
        $firstInvert = $original->invert();
        $secondInvert = $firstInvert->invert();
        
        $this->assertEquals(2700, $original->inSeconds());
        $this->assertEquals(-2700, $firstInvert->inSeconds());
        $this->assertEquals(2700, $secondInvert->inSeconds());
    }

    public function test_time_with_large_values(): void
    {
        $largeSeconds = 86400 * 365; // 1 year in seconds
        $time = new Time($largeSeconds);
        
        $this->assertEquals($largeSeconds, $time->inSeconds());
        $this->assertEquals(-$largeSeconds, $time->invert()->inSeconds());
    }

    public function test_time_with_small_values(): void
    {
        $time = new Time(1); // 1 second
        
        $this->assertEquals(1, $time->inSeconds());
        $this->assertEquals(-1, $time->invert()->inSeconds());
    }

    public function test_no_setter_methods_exist(): void
    {
        $reflection = new \ReflectionClass(Time::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        $setterMethods = array_filter($methods, function ($method) {
            return str_starts_with($method->getName(), 'set');
        });

        $this->assertEmpty($setterMethods, 'Time class should not have any setter methods to maintain immutability');
    }

    public function test_constructor_parameter_is_private(): void
    {
        $reflection = new \ReflectionClass(Time::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertEquals('inSeconds', $parameters[0]->getName());
        
        // Verify property is private (promoted constructor parameter)
        $property = $reflection->getProperty('inSeconds');
        $this->assertTrue($property->isPrivate());
    }

    public function test_different_time_instances_are_independent(): void
    {
        $time1 = new Time(1000);
        $time2 = new Time(2000);
        
        $inverted1 = $time1->invert();
        $inverted2 = $time2->invert();
        
        // Original instances should be unchanged
        $this->assertEquals(1000, $time1->inSeconds());
        $this->assertEquals(2000, $time2->inSeconds());
        
        // Inverted instances should be correct
        $this->assertEquals(-1000, $inverted1->inSeconds());
        $this->assertEquals(-2000, $inverted2->inSeconds());
    }
}