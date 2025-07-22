<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Testing;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\BeforeDueBy;
use Simensen\EphemeralTodos\In;

class TestScenarioBuilder
{
    private ?string $name = null;
    private ?string $priority = null;
    private ?CarbonInterface $baseTime = null;
    private ?CarbonInterface $createTime = null;
    private ?CarbonInterface $dueTime = null;
    private ?string $timezone = null;
    private ?string $scheduleType = null;
    private ?string $scheduleTime = null;
    private ?string $scheduleDay = null;

    private function __construct()
    {
        $this->baseTime = Carbon::now();
    }

    public static function create(): self
    {
        return new self();
    }

    public static function dailyMeeting(): self
    {
        return self::create()
            ->withName('Daily Meeting')
            ->withPriority('high')
            ->daily()
            ->at('09:00');
    }

    public static function weeklyReview(): self
    {
        return self::create()
            ->withName('Weekly Review')
            ->withPriority('medium')
            ->weekly('friday')
            ->at('16:00')
            ->createHoursBefore(2);
    }

    public static function quickReminder(): self
    {
        return self::create()
            ->withName('Quick Reminder')
            ->withPriority('low')
            ->createMinutesBefore(5);
    }

    public function withName(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function withPriority(string $priority): self
    {
        $clone = clone $this;
        $clone->priority = $priority;
        return $clone;
    }

    public function withBaseTime(CarbonInterface $baseTime): self
    {
        $clone = clone $this;
        $clone->baseTime = $baseTime;
        return $clone;
    }

    public function createMinutesBefore(int $minutes): self
    {
        $clone = clone $this;
        $clone->createTime = $this->baseTime->copy()->subMinutes($minutes);
        return $clone;
    }

    public function createHoursBefore(int $hours): self
    {
        $clone = clone $this;
        $clone->createTime = $this->baseTime->copy()->subHours($hours);
        return $clone;
    }

    public function createDaysBefore(int $days): self
    {
        $clone = clone $this;
        $clone->createTime = $this->baseTime->copy()->subDays($days);
        return $clone;
    }

    public function dueMinutesAfter(int $minutes): self
    {
        $clone = clone $this;
        $clone->dueTime = $this->baseTime->copy()->addMinutes($minutes);
        return $clone;
    }

    public function dueHoursAfter(int $hours): self
    {
        $clone = clone $this;
        $clone->dueTime = $this->baseTime->copy()->addHours($hours);
        return $clone;
    }

    public function dueDaysAfter(int $days): self
    {
        $clone = clone $this;
        $clone->dueTime = $this->baseTime->copy()->addDays($days);
        return $clone;
    }

    public function withTimezone(string $timezone): self
    {
        $clone = clone $this;
        $clone->timezone = $timezone;
        return $clone;
    }

    public function daily(): self
    {
        $clone = clone $this;
        $clone->scheduleType = 'daily';
        return $clone;
    }

    public function weekly(string $day): self
    {
        $clone = clone $this;
        $clone->scheduleType = 'weekly';
        $clone->scheduleDay = strtolower($day);
        return $clone;
    }

    public function at(string $time): self
    {
        $clone = clone $this;
        $clone->scheduleTime = $time;
        return $clone;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function getBaseTime(): CarbonInterface
    {
        return $this->baseTime;
    }

    public function getCreateTime(): ?CarbonInterface
    {
        return $this->createTime;
    }

    public function getDueTime(): ?CarbonInterface
    {
        return $this->dueTime;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function getScheduleType(): ?string
    {
        return $this->scheduleType;
    }

    public function getScheduleTime(): ?string
    {
        return $this->scheduleTime;
    }

    public function getScheduleDay(): ?string
    {
        return $this->scheduleDay;
    }

    public function buildDefinition(): Definition
    {
        $definition = Definition::define();

        if ($this->name !== null) {
            $definition = $definition->withName($this->name);
        }

        if ($this->priority !== null) {
            $definition = match ($this->priority) {
                'high' => $definition->withHighPriority(),
                'medium' => $definition->withMediumPriority(),
                'low' => $definition->withLowPriority(),
                'none' => $definition->withNoPriority(),
                default => $definition->withDefaultPriority(),
            };
        }

        // Handle schedule-based configuration first
        if ($this->scheduleTime !== null && $this->scheduleType !== null) {
            $schedule = match ($this->scheduleType) {
                'daily' => Schedule::create()->dailyAt($this->scheduleTime),
                'weekly' => Schedule::create()->weeklyOn($this->convertDayNameToNumber($this->scheduleDay), $this->scheduleTime),
                default => Schedule::create()->dailyAt($this->scheduleTime),
            };
            
            // If we have explicit create/due times, use them for create/due
            if ($this->createTime !== null && $this->dueTime !== null) {
                $diffInMinutes = (int) $this->dueTime->diffInMinutes($this->createTime);
                $beforeDueBy = $this->createBeforeDueByFromMinutes($diffInMinutes);
                $definition = $definition->create($beforeDueBy)
                                        ->due($schedule);
            } elseif ($this->createTime !== null) {
                // Create time specified, use schedule for due
                $diffInMinutes = (int) $this->baseTime->diffInMinutes($this->createTime);
                $beforeDueBy = $this->createBeforeDueByFromMinutes($diffInMinutes);
                $definition = $definition->create($beforeDueBy)
                                        ->due($schedule);
            } else {
                // No specific create time, use schedule for due
                $definition = $definition->due($schedule);
            }
        } else {
            // Handle explicit create/due timing without schedule
            if ($this->createTime !== null && $this->dueTime !== null) {
                $diffInMinutes = (int) $this->dueTime->diffInMinutes($this->createTime);
                $beforeDueBy = $this->createBeforeDueByFromMinutes($diffInMinutes);
                $definition = $definition->create($beforeDueBy);
                $definition = $definition->due(Schedule::create()->dailyAt($this->dueTime->format('H:i')));
            } elseif ($this->createTime !== null) {
                $definition = $definition->create(Schedule::create()->dailyAt($this->createTime->format('H:i')));
            } elseif ($this->dueTime !== null) {
                $definition = $definition->due(Schedule::create()->dailyAt($this->dueTime->format('H:i')));
            }
        }

        return $definition;
    }

    private function createBeforeDueByFromMinutes(int $minutes): BeforeDueBy
    {
        return match ($minutes) {
            0 => BeforeDueBy::whenDue(),
            1 => BeforeDueBy::oneMinute(),
            2 => BeforeDueBy::twoMinutes(),
            5 => BeforeDueBy::fiveMinutes(),
            10 => BeforeDueBy::tenMinutes(),
            15 => BeforeDueBy::fifteenMinutes(),
            20 => BeforeDueBy::twentyMinutes(),
            30 => BeforeDueBy::thirtyMinutes(),
            45 => BeforeDueBy::fortyFiveMinutes(),
            60 => BeforeDueBy::oneHour(),
            90 => BeforeDueBy::ninetyMinutes(),
            120 => BeforeDueBy::twoHours(),
            180 => BeforeDueBy::threeHours(),
            240 => BeforeDueBy::fourHours(),
            360 => BeforeDueBy::sixHours(),
            720 => BeforeDueBy::twelveHours(),
            1440 => BeforeDueBy::oneDay(),
            default => BeforeDueBy::fifteenMinutes(), // Default fallback
        };
    }

    private function convertDayNameToNumber(string $dayName): int
    {
        return match (strtolower($dayName)) {
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            default => 1, // Default to Monday
        };
    }

    public function __clone(): void
    {
        if ($this->baseTime !== null) {
            $this->baseTime = $this->baseTime->copy();
        }
        if ($this->createTime !== null) {
            $this->createTime = $this->createTime->copy();
        }
        if ($this->dueTime !== null) {
            $this->dueTime = $this->dueTime->copy();
        }
    }
}