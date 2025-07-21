<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\BeforeDueBy;
use Simensen\EphemeralTodos\FinalizedDefinition;
use Simensen\EphemeralTodos\In;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Schedulish;

class FinalizedDefinitionTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow('2024-01-15 10:00:00 UTC');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    private function schedule($schedule): Schedulish
    {
        return new Schedulish($schedule);
    }

    private function time($time): Schedulish
    {
        return new Schedulish($time->toTime());
    }

    public function testConstructorWithAllParameters()
    {
        $definition = new FinalizedDefinition(
            'Test Task',
            $this->schedule(Schedule::create()->daily()->at('14:00')),
            2,
            $this->schedule(Schedule::create()->daily()->at('16:00')),
            'Test description',
            AfterDueBy::oneHour()->toTime(),
            AfterDueBy::oneDay()->toTime(),
            AfterExistingFor::oneWeek()->toTime(),
            AfterExistingFor::twoWeeks()->toTime()
        );

        $this->assertEquals('Test Task', $definition->name());
        $this->assertEquals(2, $definition->priority());
    }

    public function testShouldBeCreatedAtWithSchedule()
    {
        $definition = new FinalizedDefinition(
            'Scheduled Task',
            $this->schedule(Schedule::create()->daily()->at('14:00'))
        );

        // Should be created at 14:00
        $this->assertTrue($definition->shouldBeCreatedAt(Carbon::parse('2024-01-15 14:00:00')));

        // Should not be created at other times
        $this->assertFalse($definition->shouldBeCreatedAt(Carbon::parse('2024-01-15 13:00:00')));
        $this->assertFalse($definition->shouldBeCreatedAt(Carbon::parse('2024-01-15 15:00:00')));
    }

    public function testShouldBeCreatedAtWithBeforeDueBy()
    {
        $definition = new FinalizedDefinition(
            'Before Due Task',
            $this->time(BeforeDueBy::oneHour()),
            null,
            $this->schedule(Schedule::create()->daily()->at('16:00'))
        );

        // Should be created at 15:00 (1 hour before 16:00 due time)
        $this->assertTrue($definition->shouldBeCreatedAt(Carbon::parse('2024-01-15 15:00:00')));

        // Should not be created at other times
        $this->assertFalse($definition->shouldBeCreatedAt(Carbon::parse('2024-01-15 14:00:00')));
        $this->assertFalse($definition->shouldBeCreatedAt(Carbon::parse('2024-01-15 16:00:00')));
    }

    public function testShouldBeDueAtWithSchedule()
    {
        $definition = new FinalizedDefinition(
            'Due Task',
            $this->schedule(Schedule::create()->daily()->at('14:00')),
            null,
            $this->schedule(Schedule::create()->daily()->at('16:00'))
        );

        // Should be due at 16:00
        $this->assertTrue($definition->shouldBeDueAt(Carbon::parse('2024-01-15 16:00:00')));

        // Should not be due at other times
        $this->assertFalse($definition->shouldBeDueAt(Carbon::parse('2024-01-15 14:00:00')));
        $this->assertFalse($definition->shouldBeDueAt(Carbon::parse('2024-01-15 17:00:00')));
    }

    public function testShouldBeDueAtWithNoDueSchedule()
    {
        $definition = new FinalizedDefinition(
            'No Due Task',
            $this->schedule(Schedule::create()->daily()->at('14:00'))
        );

        // Should never be due if no due schedule is set
        $this->assertFalse($definition->shouldBeDueAt(Carbon::parse('2024-01-15 14:00:00')));
        $this->assertFalse($definition->shouldBeDueAt(Carbon::parse('2024-01-15 16:00:00')));
    }

    public function testShouldBeDueAtWithInObject()
    {
        $definition = new FinalizedDefinition(
            'In Due Task',
            $this->schedule(Schedule::create()->daily()->at('14:00')),
            null,
            $this->time(In::oneHour())
        );

        // Should be due 1 hour after create time (15:00)
        $this->assertTrue($definition->shouldBeDueAt(Carbon::parse('2024-01-15 15:00:00')));

        // Should not be due at other times
        $this->assertFalse($definition->shouldBeDueAt(Carbon::parse('2024-01-15 14:00:00')));
        $this->assertFalse($definition->shouldBeDueAt(Carbon::parse('2024-01-15 16:00:00')));
    }

    public function testNextInstanceWithScheduleCreate()
    {
        $definition = new FinalizedDefinition(
            'Instance Task',
            $this->schedule(Schedule::create()->daily()->at('14:00')),
            3,
            $this->schedule(Schedule::create()->daily()->at('16:00')),
            'Task description'
        );

        $todo = $definition->nextInstance(Carbon::parse('2024-01-15 14:00:00'));

        $this->assertEquals('Instance Task', $todo->name());
        $this->assertEquals(3, $todo->priority());
        $this->assertEquals('Task description', $todo->description());
        $this->assertEquals(Carbon::parse('2024-01-15 14:00:00'), $todo->createAt());
        $this->assertEquals(Carbon::parse('2024-01-15 16:00:00'), $todo->dueAt());
    }

    public function testNextInstanceWithBeforeDueByCreate()
    {
        $definition = new FinalizedDefinition(
            'Before Due Instance',
            $this->time(BeforeDueBy::thirtyMinutes()),
            1,
            $this->schedule(Schedule::create()->daily()->at('18:00'))
        );

        $todo = $definition->nextInstance(Carbon::parse('2024-01-15 17:30:00'));

        $this->assertEquals('Before Due Instance', $todo->name());
        $this->assertEquals(1, $todo->priority());
        $this->assertEquals(Carbon::parse('2024-01-15 17:30:00'), $todo->createAt());
        $this->assertEquals(Carbon::parse('2024-01-15 18:00:00'), $todo->dueAt());
    }

    public function testNextInstanceWithDeletionRules()
    {
        $definition = new FinalizedDefinition(
            'Deletion Task',
            $this->schedule(Schedule::create()->daily()->at('12:00')),
            null,
            $this->schedule(Schedule::create()->daily()->at('14:00')),
            null,
            AfterDueBy::oneHour()->toTime(),
            AfterDueBy::oneDay()->toTime(),
            AfterExistingFor::oneWeek()->toTime(),
            AfterExistingFor::twoWeeks()->toTime()
        );

        $todo = $definition->nextInstance(Carbon::parse('2024-01-15 12:00:00'));

        // Check deletion times are calculated correctly
        $this->assertEquals(
            Carbon::parse('2024-01-15 15:00:00'), // 1 hour after due
            $todo->automaticallyDeleteWhenCompleteAndAfterDueAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-16 14:00:00'), // 1 day after due
            $todo->automaticallyDeleteWhenIncompleteAndAfterDueAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-22 12:00:00'), // 1 week after create
            $todo->automaticallyDeleteWhenCompleteAndAfterExistingAt()
        );
        $this->assertEquals(
            Carbon::parse('2024-01-29 12:00:00'), // 2 weeks after create
            $todo->automaticallyDeleteWhenIncompleteAndAfterExistingAt()
        );
    }

    public function testNextInstanceWithInDue()
    {
        $definition = new FinalizedDefinition(
            'In Due Task',
            $this->schedule(Schedule::create()->daily()->at('10:00')),
            null,
            $this->time(In::twoHours())
        );

        $todo = $definition->nextInstance(Carbon::parse('2024-01-15 10:00:00'));

        $this->assertEquals(Carbon::parse('2024-01-15 10:00:00'), $todo->createAt());
        $this->assertEquals(Carbon::parse('2024-01-15 12:00:00'), $todo->dueAt()); // 2 hours later
    }

    public function testNextInstanceWithNullWhen()
    {
        $definition = new FinalizedDefinition(
            'Current Time Task',
            $this->schedule(Schedule::create()->daily()->at('10:00'))
        );

        // Using null should use current time (10:00 in our test setup)
        $todo = $definition->nextInstance(null);

        $this->assertEquals('Current Time Task', $todo->name());
        $this->assertEquals(Carbon::parse('2024-01-15 10:00:00'), $todo->createAt());
    }

    public function testCalculateCreateWhenDueAtWithDueSchedule()
    {
        $definition = new FinalizedDefinition(
            'Calculate Create Task',
            $this->time(BeforeDueBy::oneHour()),
            null,
            $this->schedule(Schedule::create()->daily()->at('15:00'))
        );

        // Test that it calculates create time correctly (1 hour before due)
        $this->assertTrue($definition->shouldBeCreatedAt(Carbon::parse('2024-01-15 14:00:00')));
    }

    public function testCalculateDueWhenCreateAtWithInDue()
    {
        $definition = new FinalizedDefinition(
            'Calculate Due Task',
            $this->schedule(Schedule::create()->daily()->at('10:00')),
            null,
            $this->time(In::threeHours())
        );

        // Should be due 3 hours after create time (13:00)
        $this->assertTrue($definition->shouldBeDueAt(Carbon::parse('2024-01-15 13:00:00')));
    }

    public function testEdgeCaseWithAllNullOptionalParameters()
    {
        $definition = new FinalizedDefinition(
            'Minimal Task',
            $this->schedule(Schedule::create()->daily()->at('12:00'))
        );

        $todo = $definition->nextInstance(Carbon::parse('2024-01-15 12:00:00'));

        $this->assertEquals('Minimal Task', $todo->name());
        $this->assertNull($todo->priority());
        $this->assertNull($todo->description());
        $this->assertNull($todo->dueAt());
        $this->assertFalse($todo->shouldEventuallyBeDeleted());
    }

    public function testComplexSchedulingScenario()
    {
        $definition = new FinalizedDefinition(
            'Complex Task',
            $this->time(BeforeDueBy::fifteenMinutes()),
            4,
            $this->schedule(Schedule::create()->daily()->at('16:30')),
            'Complex scheduling test',
            AfterDueBy::thirtyMinutes()->toTime(),
            AfterDueBy::twoHours()->toTime(),
            AfterExistingFor::oneDay()->toTime(),
            AfterExistingFor::threeDays()->toTime()
        );

        // Should be created at 16:15 (15 minutes before 16:30 due time)
        $this->assertTrue($definition->shouldBeCreatedAt(Carbon::parse('2024-01-15 16:15:00')));

        // Should be due at 16:30
        $this->assertTrue($definition->shouldBeDueAt(Carbon::parse('2024-01-15 16:30:00')));

        $todo = $definition->nextInstance(Carbon::parse('2024-01-15 16:15:00'));

        $this->assertEquals('Complex Task', $todo->name());
        $this->assertEquals(4, $todo->priority());
        $this->assertEquals('Complex scheduling test', $todo->description());
        $this->assertEquals(Carbon::parse('2024-01-15 16:15:00'), $todo->createAt());
        $this->assertEquals(Carbon::parse('2024-01-15 16:30:00'), $todo->dueAt());
        $this->assertTrue($todo->shouldEventuallyBeDeleted());
    }
}
