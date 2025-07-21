<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use LogicException;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Schedulish;
use Simensen\EphemeralTodos\Time;

class SchedulishTest extends TestCase
{
    public function testConstructorWithSchedule()
    {
        $schedule = Schedule::create()->daily()->at('14:00');
        $schedulish = new Schedulish($schedule);

        $this->assertTrue($schedulish->isSchedule());
        $this->assertFalse($schedulish->isTime());
    }

    public function testConstructorWithTime()
    {
        $time = new Time(3600); // 1 hour
        $schedulish = new Schedulish($time);

        $this->assertFalse($schedulish->isSchedule());
        $this->assertTrue($schedulish->isTime());
    }

    public function testScheduleMethodReturnsSchedule()
    {
        $schedule = Schedule::create()->daily()->at('14:00');
        $schedulish = new Schedulish($schedule);

        $retrievedSchedule = $schedulish->schedule();
        $this->assertSame($schedule, $retrievedSchedule);
        $this->assertEquals('0 14 * * *', $retrievedSchedule->cronExpression());
    }

    public function testScheduleMethodThrowsExceptionWhenTime()
    {
        $time = new Time(3600);
        $schedulish = new Schedulish($time);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Requested the Schedule associated with this Schedulish, but there is no Schedule associated with it.');

        $schedulish->schedule();
    }

    public function testTimeMethodReturnsTime()
    {
        $time = new Time(7200); // 2 hours
        $schedulish = new Schedulish($time);

        $retrievedTime = $schedulish->time();
        $this->assertSame($time, $retrievedTime);
        $this->assertEquals(7200, $retrievedTime->inSeconds());
    }

    public function testTimeMethodThrowsExceptionWhenSchedule()
    {
        $schedule = Schedule::create()->weekly()->mondays()->at('09:00');
        $schedulish = new Schedulish($schedule);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Requested the Time associated with this Schedulish, but there is no Time associated with it.');

        $schedulish->time();
    }

    public function testSameAsWithIdenticalSchedules()
    {
        $schedule1 = Schedule::create()->daily()->at('12:00');
        $schedule2 = Schedule::create()->daily()->at('12:00');

        $schedulish1 = new Schedulish($schedule1);
        $schedulish2 = new Schedulish($schedule2);

        $this->assertTrue($schedulish1->sameAs($schedulish2));
    }

    public function testSameAsWithDifferentSchedules()
    {
        $schedule1 = Schedule::create()->daily()->at('12:00');
        $schedule2 = Schedule::create()->daily()->at('14:00');

        $schedulish1 = new Schedulish($schedule1);
        $schedulish2 = new Schedulish($schedule2);

        $this->assertFalse($schedulish1->sameAs($schedulish2));
    }

    public function testSameAsWithIdenticalTimes()
    {
        $time1 = new Time(3600);
        $time2 = new Time(3600);

        $schedulish1 = new Schedulish($time1);
        $schedulish2 = new Schedulish($time2);

        $this->assertTrue($schedulish1->sameAs($schedulish2));
    }

    public function testSameAsWithDifferentTimes()
    {
        $time1 = new Time(3600);
        $time2 = new Time(7200);

        $schedulish1 = new Schedulish($time1);
        $schedulish2 = new Schedulish($time2);

        $this->assertFalse($schedulish1->sameAs($schedulish2));
    }

    public function testSameAsWithScheduleAndTime()
    {
        $schedule = Schedule::create()->daily()->at('12:00');
        $time = new Time(3600);

        $schedulish1 = new Schedulish($schedule);
        $schedulish2 = new Schedulish($time);

        $this->assertFalse($schedulish1->sameAs($schedulish2));
        $this->assertFalse($schedulish2->sameAs($schedulish1));
    }

    public function testSameAsWithRawTimeObject()
    {
        $time1 = new Time(1800);
        $time2 = new Time(1800);

        $schedulish = new Schedulish($time1);

        // Should auto-wrap the raw Time object
        $this->assertTrue($schedulish->sameAs($time2));
    }

    public function testSameAsWithRawScheduleObject()
    {
        $schedule1 = Schedule::create()->weekly()->fridays()->at('17:00');
        $schedule2 = Schedule::create()->weekly()->fridays()->at('17:00');

        $schedulish = new Schedulish($schedule1);

        // Should auto-wrap the raw Schedule object
        $this->assertTrue($schedulish->sameAs($schedule2));
    }

    public function testSameAsWithNull()
    {
        $schedule = Schedule::create()->daily()->at('10:00');
        $schedulish = new Schedulish($schedule);

        $this->assertFalse($schedulish->sameAs(null));
    }

    public function testSameAsWithNonSchedulishObject()
    {
        $schedule = Schedule::create()->daily()->at('10:00');
        $schedulish = new Schedulish($schedule);

        $this->assertFalse($schedulish->sameAs('not a schedulish'));
        $this->assertFalse($schedulish->sameAs(42));
        $this->assertFalse($schedulish->sameAs([]));
        $this->assertFalse($schedulish->sameAs(new \stdClass()));
    }

    public function testTypeCheckingConsistency()
    {
        $schedule = Schedule::create()->monthly();
        $time = new Time(86400);

        $scheduleSchedulish = new Schedulish($schedule);
        $timeSchedulish = new Schedulish($time);

        // Schedule schedulish should only be schedule
        $this->assertTrue($scheduleSchedulish->isSchedule());
        $this->assertFalse($scheduleSchedulish->isTime());

        // Time schedulish should only be time
        $this->assertFalse($timeSchedulish->isSchedule());
        $this->assertTrue($timeSchedulish->isTime());
    }

    public function testComplexScheduleComparison()
    {
        $complexSchedule1 = Schedule::create()
            ->withCronExpression('0 9-17 * * 1-5')
            ->when(fn () => true);

        $complexSchedule2 = Schedule::create()
            ->withCronExpression('0 9-17 * * 1-5')
            ->when(fn () => false); // Different filter, but same cron expression

        $schedulish1 = new Schedulish($complexSchedule1);
        $schedulish2 = new Schedulish($complexSchedule2);

        // Should be same because only cron expression is compared
        $this->assertTrue($schedulish1->sameAs($schedulish2));
    }
}
