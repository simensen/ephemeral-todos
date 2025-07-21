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

    public function testEqualToTheMinuteWithSameMinuteDifferentSeconds()
    {
        $first = Carbon::parse('2024-01-15 10:30:15');
        $second = Carbon::parse('2024-01-15 10:30:45');

        $this->assertTrue(Utils::equalToTheMinute($first, $second));
    }

    public function testEqualToTheMinuteWithDifferentMinutes()
    {
        $first = Carbon::parse('2024-01-15 10:30:15');
        $second = Carbon::parse('2024-01-15 10:31:15');

        $this->assertFalse(Utils::equalToTheMinute($first, $second));
    }

    public function testEqualToTheMinuteWithSameExactTime()
    {
        $time = Carbon::parse('2024-01-15 10:30:00');

        $this->assertTrue(Utils::equalToTheMinute($time, $time));
    }

    public function testEqualToTheMinuteWithDifferentMicroseconds()
    {
        $first = Carbon::parse('2024-01-15 10:30:30.123456');
        $second = Carbon::parse('2024-01-15 10:30:30.789012');

        $this->assertTrue(Utils::equalToTheMinute($first, $second));
    }

    public function testEqualToTheMinuteWithStringInputs()
    {
        $first = '2024-01-15 10:30:15';
        $second = '2024-01-15 10:30:45';

        $this->assertTrue(Utils::equalToTheMinute($first, $second));
    }

    public function testEqualToTheMinuteWithDatetimeObjects()
    {
        $first = new DateTime('2024-01-15 10:30:15');
        $second = new DateTimeImmutable('2024-01-15 10:30:45');

        $this->assertTrue(Utils::equalToTheMinute($first, $second));
    }

    public function testEqualToTheMinuteWithMixedTypes()
    {
        $carbonTime = Carbon::parse('2024-01-15 10:30:15');
        $stringTime = '2024-01-15 10:30:45';
        $dateTime = new DateTime('2024-01-15 10:30:30');

        $this->assertTrue(Utils::equalToTheMinute($carbonTime, $stringTime));
        $this->assertTrue(Utils::equalToTheMinute($stringTime, $dateTime));
        $this->assertTrue(Utils::equalToTheMinute($carbonTime, $dateTime));
    }

    public function testEqualToTheMinuteWithNullValues()
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

    public function testToCarbonWithCarbonObject()
    {
        $carbon = Carbon::parse('2024-01-15 10:30:15');
        $result = Utils::toCarbon($carbon);

        $this->assertSame($carbon, $result);
        $this->assertInstanceOf(Carbon::class, $result);
    }

    public function testToCarbonWithString()
    {
        $result = Utils::toCarbon('2024-01-15 10:30:15');

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2024-01-15 10:30:15', $result->format('Y-m-d H:i:s'));
    }

    public function testToCarbonWithDatetime()
    {
        $dateTime = new DateTime('2024-01-15 10:30:15');
        $result = Utils::toCarbon($dateTime);

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2024-01-15 10:30:15', $result->format('Y-m-d H:i:s'));
    }

    public function testToCarbonWithDatetimeImmutable()
    {
        $dateTime = new DateTimeImmutable('2024-01-15 10:30:15');
        $result = Utils::toCarbon($dateTime);

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2024-01-15 10:30:15', $result->format('Y-m-d H:i:s'));
    }

    public function testToCarbonWithNull()
    {
        $result = Utils::toCarbon(null);

        $this->assertInstanceOf(Carbon::class, $result);
        // When null is passed to Carbon constructor, it creates "now"
        $this->assertEquals(Carbon::now(), $result);
    }

    public function testToCarbonWithTimezone()
    {
        $timezone = new DateTimeZone('America/New_York');
        $result = Utils::toCarbon('2024-01-15 10:30:15', $timezone);

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('America/New_York', $result->getTimezone()->getName());
    }

    public function testToCarbonWithDifferentTimezones()
    {
        $utcTimezone = new DateTimeZone('UTC');
        $nyTimezone = new DateTimeZone('America/New_York');

        $utcTime = Utils::toCarbon('2024-01-15 10:30:15', $utcTimezone);
        $nyTime = Utils::toCarbon('2024-01-15 10:30:15', $nyTimezone);

        $this->assertEquals('UTC', $utcTime->getTimezone()->getName());
        $this->assertEquals('America/New_York', $nyTime->getTimezone()->getName());
        $this->assertNotEquals($utcTime->getTimestamp(), $nyTime->getTimestamp());
    }

    public function testToCarbonPreservesOriginalCarbonTimezone()
    {
        $timezone = new DateTimeZone('Europe/London');
        $originalCarbon = Carbon::parse('2024-01-15 10:30:15', $timezone);

        // When passing a Carbon object, timezone parameter should be ignored
        $result = Utils::toCarbon($originalCarbon, new DateTimeZone('America/New_York'));

        $this->assertSame($originalCarbon, $result);
        $this->assertEquals('Europe/London', $result->getTimezone()->getName());
    }

    public function testEqualToTheMinuteEdgeCases()
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

    public function testToCarbonWithRelativeStrings()
    {
        // Test with relative time strings
        $result = Utils::toCarbon('now');
        $this->assertInstanceOf(Carbon::class, $result);

        $result = Utils::toCarbon('+1 hour');
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals(Carbon::now()->addHour()->format('Y-m-d H'), $result->format('Y-m-d H'));
    }

    public function testEqualToTheMinuteDoesNotMutateOriginalObjects()
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
