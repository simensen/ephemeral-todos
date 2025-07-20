<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeZone;
use Simensen\EphemeralTodos\Tests\TestCase;
use Simensen\EphemeralTodos\Utils;

class UtilsTest extends TestCase
{
    public function test_equal_to_the_minute_with_same_times(): void
    {
        $time1 = Carbon::parse('2025-01-19 12:30:15');
        $time2 = Carbon::parse('2025-01-19 12:30:45');
        
        $this->assertTrue(Utils::equalToTheMinute($time1, $time2));
    }

    public function test_equal_to_the_minute_with_different_minutes(): void
    {
        $time1 = Carbon::parse('2025-01-19 12:30:15');
        $time2 = Carbon::parse('2025-01-19 12:31:15');
        
        $this->assertFalse(Utils::equalToTheMinute($time1, $time2));
    }

    public function test_equal_to_the_minute_with_different_hours(): void
    {
        $time1 = Carbon::parse('2025-01-19 12:30:15');
        $time2 = Carbon::parse('2025-01-19 13:30:15');
        
        $this->assertFalse(Utils::equalToTheMinute($time1, $time2));
    }

    public function test_equal_to_the_minute_with_carbon_and_datetime_interface(): void
    {
        $carbon = Carbon::parse('2025-01-19 12:30:15');
        $datetime = new DateTimeImmutable('2025-01-19 12:30:45');
        
        $this->assertTrue(Utils::equalToTheMinute($carbon, $datetime));
    }

    public function test_equal_to_the_minute_with_string_dates(): void
    {
        $this->assertTrue(Utils::equalToTheMinute('2025-01-19 12:30:15', '2025-01-19 12:30:45'));
        $this->assertFalse(Utils::equalToTheMinute('2025-01-19 12:30:15', '2025-01-19 12:31:15'));
    }

    public function test_equal_to_the_minute_with_null_values(): void
    {
        $this->travelTo('2025-01-19 12:30:15');
        
        // Both null should equal current time to the minute
        $this->assertTrue(Utils::equalToTheMinute(null, null));
        
        // One null should equal current time
        $currentTime = Carbon::parse('2025-01-19 12:30:45');
        $this->assertTrue(Utils::equalToTheMinute(null, $currentTime));
        $this->assertTrue(Utils::equalToTheMinute($currentTime, null));
    }

    public function test_equal_to_the_minute_ignores_seconds_and_microseconds(): void
    {
        $time1 = Carbon::parse('2025-01-19 12:30:15.123456');
        $time2 = Carbon::parse('2025-01-19 12:30:59.987654');
        
        $this->assertTrue(Utils::equalToTheMinute($time1, $time2));
    }

    public function test_to_carbon_with_carbon_instance(): void
    {
        $original = Carbon::parse('2025-01-19 12:30:00');
        $result = Utils::toCarbon($original);
        
        $this->assertSame($original, $result);
    }

    public function test_to_carbon_with_datetime_interface(): void
    {
        $datetime = new DateTimeImmutable('2025-01-19 12:30:00');
        $result = Utils::toCarbon($datetime);
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2025-01-19 12:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function test_to_carbon_with_string(): void
    {
        $result = Utils::toCarbon('2025-01-19 12:30:00');
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2025-01-19 12:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function test_to_carbon_with_null(): void
    {
        $this->travelTo('2025-01-19 12:30:00');
        
        $result = Utils::toCarbon(null);
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2025-01-19 12:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function test_to_carbon_with_timezone(): void
    {
        $timezone = new DateTimeZone('America/New_York');
        $result = Utils::toCarbon('2025-01-19 12:30:00', $timezone);
        
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('America/New_York', $result->getTimezone()->getName());
    }

    public function test_to_carbon_with_timezone_and_carbon_instance(): void
    {
        $timezone = new DateTimeZone('America/New_York');
        $original = Carbon::parse('2025-01-19 12:30:00', 'UTC');
        $result = Utils::toCarbon($original, $timezone);
        
        // Should return the original Carbon instance unchanged
        $this->assertSame($original, $result);
        $this->assertEquals('UTC', $result->getTimezone()->getName());
    }

    public function test_to_carbon_with_different_timezones(): void
    {
        $utc = new DateTimeZone('UTC');
        $est = new DateTimeZone('America/New_York');
        $pst = new DateTimeZone('America/Los_Angeles');
        
        $utcResult = Utils::toCarbon('2025-01-19 12:30:00', $utc);
        $estResult = Utils::toCarbon('2025-01-19 12:30:00', $est);
        $pstResult = Utils::toCarbon('2025-01-19 12:30:00', $pst);
        
        $this->assertEquals('UTC', $utcResult->getTimezone()->getName());
        $this->assertEquals('America/New_York', $estResult->getTimezone()->getName());
        $this->assertEquals('America/Los_Angeles', $pstResult->getTimezone()->getName());
    }

    public function test_equal_to_the_minute_preserves_original_instances(): void
    {
        $time1 = Carbon::parse('2025-01-19 12:30:15.123456');
        $time2 = Carbon::parse('2025-01-19 12:30:45.987654');
        
        $originalTime1Format = $time1->format('Y-m-d H:i:s.u');
        $originalTime2Format = $time2->format('Y-m-d H:i:s.u');
        
        Utils::equalToTheMinute($time1, $time2);
        
        // Original instances should be unchanged (method uses clone())
        $this->assertEquals($originalTime1Format, $time1->format('Y-m-d H:i:s.u'));
        $this->assertEquals($originalTime2Format, $time2->format('Y-m-d H:i:s.u'));
    }

    public function test_equal_to_the_minute_with_mixed_types(): void
    {
        $carbon = Carbon::parse('2025-01-19 12:30:15');
        $datetime = new DateTimeImmutable('2025-01-19 12:30:45');
        $string = '2025-01-19 12:30:30';
        
        $this->assertTrue(Utils::equalToTheMinute($carbon, $datetime));
        $this->assertTrue(Utils::equalToTheMinute($carbon, $string));
        $this->assertTrue(Utils::equalToTheMinute($datetime, $string));
    }

    public function test_to_carbon_edge_cases(): void
    {
        // Test with various string formats
        $iso = Utils::toCarbon('2025-01-19T12:30:00Z');
        $this->assertInstanceOf(Carbon::class, $iso);
        
        $relative = Utils::toCarbon('tomorrow');
        $this->assertInstanceOf(Carbon::class, $relative);
        
        $timestamp = Utils::toCarbon('@1642597800'); // Unix timestamp
        $this->assertInstanceOf(Carbon::class, $timestamp);
    }
}