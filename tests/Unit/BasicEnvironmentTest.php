<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use Carbon\Carbon;
use Simensen\EphemeralTodos\Tests\TestCase;

class BasicEnvironmentTest extends TestCase
{
    public function test_environment_setup(): void
    {
        // Test that Carbon test time is set correctly
        $this->assertEquals('2025-01-19 12:00:00', $this->now()->format('Y-m-d H:i:s'));
    }

    public function test_time_travel_helpers(): void
    {
        // Test traveling to a specific time
        $this->travelTo('2025-01-20 15:30:00');
        $this->assertEquals('2025-01-20 15:30:00', $this->now()->format('Y-m-d H:i:s'));

        // Test traveling forward
        $this->travel('2 hours');
        $this->assertEquals('2025-01-20 17:30:00', $this->now()->format('Y-m-d H:i:s'));

        // Test traveling backward
        $this->travelBack('30 minutes');
        $this->assertEquals('2025-01-20 17:00:00', $this->now()->format('Y-m-d H:i:s'));
    }

    public function test_carbon_equal_to_minute_assertion(): void
    {
        $time1 = Carbon::parse('2025-01-19 12:00:15');
        $time2 = Carbon::parse('2025-01-19 12:00:45');
        
        // Should pass because they're equal to the minute
        $this->assertCarbonEqualToMinute($time1, $time2);
    }

    public function test_timezone_helper(): void
    {
        $utcTime = $this->carbonInTimezone('2025-01-19 12:00:00', 'UTC');
        $estTime = $this->carbonInTimezone('2025-01-19 12:00:00', 'America/New_York');
        
        $this->assertEquals('UTC', $utcTime->getTimezone()->getName());
        $this->assertEquals('America/New_York', $estTime->getTimezone()->getName());
    }
}