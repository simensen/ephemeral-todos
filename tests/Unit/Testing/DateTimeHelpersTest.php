<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\Tests\Testing\DateTimeHelpers;

class DateTimeHelpersTest extends TestCase
{
    use DateTimeHelpers;

    public function testCreateTestDateTime()
    {
        $dateTime = $this->createTestDateTime();
        
        $this->assertInstanceOf(DateTimeImmutable::class, $dateTime);
        $this->assertEquals('2025-01-19 12:00:00', $dateTime->format('Y-m-d H:i:s'));
    }

    public function testCreateTestDateTimeWithCustomTime()
    {
        $dateTime = $this->createTestDateTime('2025-06-15 14:30:00');
        
        $this->assertEquals('2025-06-15 14:30:00', $dateTime->format('Y-m-d H:i:s'));
    }

    public function testCreateDueDate()
    {
        $dueDate = $this->createDueDate();
        
        $this->assertInstanceOf(DateTimeImmutable::class, $dueDate);
        $this->assertEquals('2025-01-19 15:00:00', $dueDate->format('Y-m-d H:i:s'));
    }

    public function testCreateDueDateWithCustomTime()
    {
        $dueDate = $this->createDueDate('2025-12-25 09:00:00');
        
        $this->assertEquals('2025-12-25 09:00:00', $dueDate->format('Y-m-d H:i:s'));
    }

    public function testCreateDeleteDate()
    {
        $deleteDate = $this->createDeleteDate();
        
        $this->assertInstanceOf(DateTimeImmutable::class, $deleteDate);
        $this->assertEquals('2025-01-20 12:00:00', $deleteDate->format('Y-m-d H:i:s'));
    }

    public function testCreateDeleteDateWithCustomTime()
    {
        $deleteDate = $this->createDeleteDate('2025-07-04 18:00:00');
        
        $this->assertEquals('2025-07-04 18:00:00', $deleteDate->format('Y-m-d H:i:s'));
    }

    public function testCreateDateTimeInTimezone()
    {
        $dateTime = $this->createDateTimeInTimezone('2025-01-19 12:00:00', 'America/New_York');
        
        $this->assertInstanceOf(DateTimeImmutable::class, $dateTime);
        $this->assertEquals('America/New_York', $dateTime->getTimezone()->getName());
        $this->assertEquals('2025-01-19 12:00:00', $dateTime->format('Y-m-d H:i:s'));
    }

    public function testCreateDateTimeInTimezoneWithDefault()
    {
        $dateTime = $this->createDateTimeInTimezone();
        
        $this->assertEquals('UTC', $dateTime->getTimezone()->getName());
        $this->assertEquals('2025-01-19 12:00:00', $dateTime->format('Y-m-d H:i:s'));
    }

    public function testCreateTodoDateTimeSequence()
    {
        $createAt = $this->createTestDateTime();
        $dueAt = $this->createDueDate();
        $deleteAt = $this->createDeleteDate();
        
        // Test that they have proper temporal sequence
        $this->assertTrue($createAt < $dueAt);
        $this->assertTrue($dueAt < $deleteAt);
    }

    public function testCreateDateTimeWithInvalidTimezone()
    {
        $this->expectException(\Exception::class);
        $this->createDateTimeInTimezone('2025-01-19 12:00:00', 'Invalid/Timezone');
    }

    public function testHelpersCreateImmutableInstances()
    {
        $dateTime1 = $this->createTestDateTime();
        $dateTime2 = $dateTime1->modify('+1 hour');
        
        // Original should be unchanged
        $this->assertEquals('2025-01-19 12:00:00', $dateTime1->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-01-19 13:00:00', $dateTime2->format('Y-m-d H:i:s'));
    }

    public function testHelperMethodsHaveSemanticNames()
    {
        // Test that method names clearly indicate their purpose
        $createAt = $this->createTestDateTime();  // For general test use
        $dueAt = $this->createDueDate();         // For todo due dates
        $deleteAt = $this->createDeleteDate();   // For deletion dates
        
        $this->assertInstanceOf(DateTimeImmutable::class, $createAt);
        $this->assertInstanceOf(DateTimeImmutable::class, $dueAt);
        $this->assertInstanceOf(DateTimeImmutable::class, $deleteAt);
    }
}