<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Testing;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Trait providing helper methods for creating DateTimeImmutable instances in tests.
 * 
 * This trait eliminates repetitive DateTimeImmutable creation patterns and provides
 * semantic methods for different types of test dates (create, due, delete, etc.).
 * 
 * Usage:
 *   class MyTest extends TestCase
 *   {
 *       use DateTimeHelpers;
 *       
 *       public function testSomething()
 *       {
 *           $createAt = $this->createTestDateTime();
 *           $dueAt = $this->createDueDate();
 *           // ... use in test
 *       }
 *   }
 */
trait DateTimeHelpers
{
    /**
     * Create a DateTimeImmutable for general test use.
     * 
     * @param string $time Time string, defaults to standard test time
     */
    protected function createTestDateTime(string $time = '2025-01-19 12:00:00'): DateTimeImmutable
    {
        return new DateTimeImmutable($time);
    }

    /**
     * Create a DateTimeImmutable suitable for todo due dates.
     * 
     * @param string $time Time string, defaults to 3 hours after test time
     */
    protected function createDueDate(string $time = '2025-01-19 15:00:00'): DateTimeImmutable
    {
        return new DateTimeImmutable($time);
    }

    /**
     * Create a DateTimeImmutable suitable for deletion dates.
     * 
     * @param string $time Time string, defaults to 1 day after test time
     */
    protected function createDeleteDate(string $time = '2025-01-20 12:00:00'): DateTimeImmutable
    {
        return new DateTimeImmutable($time);
    }

    /**
     * Create a DateTimeImmutable in a specific timezone.
     * 
     * @param string $time Time string, defaults to standard test time
     * @param string $timezone Timezone string, defaults to UTC
     */
    protected function createDateTimeInTimezone(
        string $time = '2025-01-19 12:00:00',
        string $timezone = 'UTC'
    ): DateTimeImmutable {
        return new DateTimeImmutable($time, new DateTimeZone($timezone));
    }

    /**
     * Create a sequence of DateTimeImmutable instances for common todo scenarios.
     * 
     * Returns an array with keys: 'create', 'due', 'deleteComplete', 'deleteIncomplete'
     * 
     * @param string|null $baseTime Base time for sequence, defaults to standard test time
     */
    protected function createTodoDateSequence(?string $baseTime = null): array
    {
        $base = new DateTimeImmutable($baseTime ?? '2025-01-19 12:00:00');
        
        return [
            'create' => $base,
            'due' => $base->modify('+3 hours'),
            'deleteComplete' => $base->modify('+1 day'),
            'deleteIncomplete' => $base->modify('+2 days'),
        ];
    }

    /**
     * Create multiple DateTimeImmutable instances with relative offsets.
     * 
     * @param array $offsets Array of relative time strings (e.g., ['+1 hour', '+2 hours'])
     * @param string $baseTime Base time to calculate from, defaults to standard test time
     */
    protected function createDateTimeSequence(
        array $offsets,
        string $baseTime = '2025-01-19 12:00:00'
    ): array {
        $base = new DateTimeImmutable($baseTime);
        $sequence = [$base]; // Include base time as first element
        
        foreach ($offsets as $offset) {
            $sequence[] = $base->modify($offset);
        }
        
        return $sequence;
    }
}