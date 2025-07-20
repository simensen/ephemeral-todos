<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set a fixed time for predictable testing
        Carbon::setTestNow('2025-01-19 12:00:00');
    }

    protected function tearDown(): void
    {
        // Reset Carbon's test time
        Carbon::setTestNow();
        
        parent::tearDown();
    }

    /**
     * Advance the test time by the specified amount
     */
    protected function travelTo(string $time): void
    {
        Carbon::setTestNow($time);
    }

    /**
     * Travel forward in time by the specified interval
     */
    protected function travel(string $interval): void
    {
        $newTime = Carbon::getTestNow()->add(\DateInterval::createFromDateString($interval));
        Carbon::setTestNow($newTime);
    }

    /**
     * Travel backward in time by the specified interval
     */
    protected function travelBack(string $interval): void
    {
        $newTime = Carbon::getTestNow()->sub(\DateInterval::createFromDateString($interval));
        Carbon::setTestNow($newTime);
    }

    /**
     * Get the current test time
     */
    protected function now(): CarbonInterface
    {
        return Carbon::getTestNow();
    }

    /**
     * Create a Carbon instance in a specific timezone for testing
     */
    protected function carbonInTimezone(string $time, string $timezone): Carbon
    {
        return Carbon::parse($time, $timezone);
    }

    /**
     * Assert that two Carbon instances are equal to the minute (ignoring seconds)
     */
    protected function assertCarbonEqualToMinute(Carbon $expected, Carbon $actual, string $message = ''): void
    {
        $this->assertEquals(
            $expected->format('Y-m-d H:i'),
            $actual->format('Y-m-d H:i'),
            $message ?: "Expected {$expected->toDateTimeString()}, got {$actual->toDateTimeString()}"
        );
    }
}