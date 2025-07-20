<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests;

use Carbon\Carbon;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\Utils;

class UtilsTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow('2024-01-15 10:30:45');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function test_equal_to_the_minute_with_same_minute_different_seconds()
    {
        $first = Carbon::parse('2024-01-15 10:30:15');
        $second = Carbon::parse('2024-01-15 10:30:45');
        
        $this->assertTrue(Utils::equalToTheMinute($first, $second));
    }

    public function test_equal_to_the_minute_with_different_minutes()
    {
        $first = Carbon::parse('2024-01-15 10:30:15');
        $second = Carbon::parse('2024-01-15 10:31:15');
        
        $this->assertFalse(Utils::equalToTheMinute($first, $second));
    }

    public function test_equal_to_the_minute_with_same_exact_time()
    {
        $time = Carbon::parse('2024-01-15 10:30:00');
        
        $this->assertTrue(Utils::equalToTheMinute($time, $time));
    }

    public function test_equal_to_the_minute_with_different_microseconds()
    {
        $first = Carbon::parse('2024-01-15 10:30:30.123456');
        $second = Carbon::parse('2024-01-15 10:30:30.789012');
        
        $this->assertTrue(Utils::equalToTheMinute($first, $second));
    }

    public function test_equal_to_the_minute_with_string_inputs()
    {
        $first = '2024-01-15 10:30:15';
        $second = '2024-01-15 10:30:45';
        
        $this->assertTrue(Utils::equalToTheMinute($first, $second));
    }

    public function test_equal_to_the_minute_with_datetime_objects()
    {
        $first = new DateTime('2024-01-15 10:30:15');
        $second = new DateTimeImmutable('2024-01-15 10:30:45');
        
        $this->assertTrue(Utils::equalToTheMinute($first, $second));
    }

    public function test_equal_to_the_minute_with_mixed_types()
    {
        $carbonTime = Carbon::parse('2024-01-15 10:30:15');
        $stringTime = '2024-01-15 10:30:45';
        $dateTime = new DateTime('2024-01-15 10:30:30');
        
        $this->assertTrue(Utils::equalToTheMinute($carbonTime, $stringTime));
        $this->assertTrue(Utils::equalToTheMinute($stringTime, $dateTime));
        $this->assertTrue(Utils::equalToTheMinute($carbonTime, $dateTime));
    }

    public function test_equal_to_the_minute_with_null_values()
    {
        $time = Carbon::parse('2024-01-15 10:30:15');
        
        // Both null should be equal (both become "now" when converted to Carbon)
        $this->assertTrue(Utils::equalToTheMinute(null, null));
        
        // One null (becomes "now"), one specific time - should not be equal unless we're testing at exactly that minute
        // Since we set test time to 10:30:45, null becomes 10:30:45, so comparing with 10:30:15 should be true (same minute)
        $this->assertTrue(Utils::equalToTheMinute($time, null));
        $this->assertTrue(Utils::equalToTheMinute(null, $time));
        
        // But comparing with a different minute should be false
        $differentMinute = Carbon::parse('2024-01-15 10:31:15');
        $this->assertFalse(Utils::equalToTheMinute($differentMinute, null));
        $this->assertFalse(Utils::equalToTheMinute(null, $differentMinute));
    }

    public function test_to_carbon_with_carbon_object()
    {
        $carbon = Carbon::parse('2024-01-15 10:30:15');
        $result = Utils::toCarbon($carbon);
        
        $this->assertSame($carbon, $result);
        $this->assertInstanceOf(Carbon::class, $result);
    }

    public function test_to_carbon_with_string()
    {
        $result = Utils::toCarbon('2024-01-15 10:30:15');
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2024-01-15 10:30:15', $result->format('Y-m-d H:i:s'));
    }

    public function test_to_carbon_with_datetime()
    {
        $dateTime = new DateTime('2024-01-15 10:30:15');
        $result = Utils::toCarbon($dateTime);
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2024-01-15 10:30:15', $result->format('Y-m-d H:i:s'));
    }

    public function test_to_carbon_with_datetime_immutable()
    {
        $dateTime = new DateTimeImmutable('2024-01-15 10:30:15');
        $result = Utils::toCarbon($dateTime);
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2024-01-15 10:30:15', $result->format('Y-m-d H:i:s'));
    }

    public function test_to_carbon_with_null()
    {
        $result = Utils::toCarbon(null);
        
        $this->assertInstanceOf(Carbon::class, $result);
        // When null is passed to Carbon constructor, it creates "now"
        $this->assertEquals(Carbon::now(), $result);
    }

    public function test_to_carbon_with_timezone()
    {
        $timezone = new DateTimeZone('America/New_York');
        $result = Utils::toCarbon('2024-01-15 10:30:15', $timezone);
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('America/New_York', $result->getTimezone()->getName());
    }

    public function test_to_carbon_with_different_timezones()
    {
        $utcTimezone = new DateTimeZone('UTC');
        $nyTimezone = new DateTimeZone('America/New_York');
        
        $utcTime = Utils::toCarbon('2024-01-15 10:30:15', $utcTimezone);
        $nyTime = Utils::toCarbon('2024-01-15 10:30:15', $nyTimezone);
        
        $this->assertEquals('UTC', $utcTime->getTimezone()->getName());
        $this->assertEquals('America/New_York', $nyTime->getTimezone()->getName());
        $this->assertNotEquals($utcTime->getTimestamp(), $nyTime->getTimestamp());
    }

    public function test_to_carbon_preserves_original_carbon_timezone()
    {
        $timezone = new DateTimeZone('Europe/London');
        $originalCarbon = Carbon::parse('2024-01-15 10:30:15', $timezone);
        
        // When passing a Carbon object, timezone parameter should be ignored
        $result = Utils::toCarbon($originalCarbon, new DateTimeZone('America/New_York'));
        
        $this->assertSame($originalCarbon, $result);
        $this->assertEquals('Europe/London', $result->getTimezone()->getName());
    }

    public function test_equal_to_the_minute_edge_cases()
    {
        // Test boundary conditions
        $endOfMinute = Carbon::parse('2024-01-15 10:30:59.999999');
        $startOfNextMinute = Carbon::parse('2024-01-15 10:31:00.000000');
        
        $this->assertFalse(Utils::equalToTheMinute($endOfMinute, $startOfNextMinute));
        
        // Test same minute, different seconds
        $earlyInMinute = Carbon::parse('2024-01-15 10:30:00.000000');
        $lateInMinute = Carbon::parse('2024-01-15 10:30:59.999999');
        
        $this->assertTrue(Utils::equalToTheMinute($earlyInMinute, $lateInMinute));
    }

    public function test_to_carbon_with_relative_strings()
    {
        // Test with relative time strings
        $result = Utils::toCarbon('now');
        $this->assertInstanceOf(Carbon::class, $result);
        
        $result = Utils::toCarbon('+1 hour');
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals(Carbon::now()->addHour()->format('Y-m-d H'), $result->format('Y-m-d H'));
    }

    public function test_equal_to_the_minute_does_not_mutate_original_objects()
    {
        $original1 = Carbon::parse('2024-01-15 10:30:45.123456');
        $original2 = Carbon::parse('2024-01-15 10:30:30.789012');
        
        $originalFormat1 = $original1->format('Y-m-d H:i:s.u');
        $originalFormat2 = $original2->format('Y-m-d H:i:s.u');
        
        Utils::equalToTheMinute($original1, $original2);
        
        // Verify originals weren't mutated
        $this->assertEquals($originalFormat1, $original1->format('Y-m-d H:i:s.u'));
        $this->assertEquals($originalFormat2, $original2->format('Y-m-d H:i:s.u'));
    }
}