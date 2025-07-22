<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Testing;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\BeforeDueBy;
use Simensen\EphemeralTodos\In;
use Simensen\EphemeralTodos\Todo;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use PHPUnit\Framework\Assert;

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
    private ?string $deleteAfterDueInterval = null;
    private ?string $deleteAfterDueCondition = null;
    private ?string $deleteAfterExistingInterval = null;
    private ?string $deleteAfterExistingCondition = null;
    private ?string $businessHoursStart = null;
    private ?string $businessHoursEnd = null;
    private bool $timezoneAwareScheduling = false;
    private bool $timezoneAwareBusinessHours = false;
    private bool $timezoneAwareDeletion = false;

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

    public function deleteAfterDue(string $interval, string $condition = 'either'): self
    {
        $clone = clone $this;
        $clone->deleteAfterDueInterval = $interval;
        $clone->deleteAfterDueCondition = $condition;
        return $clone;
    }

    public function deleteAfterExisting(string $interval, string $condition = 'either'): self
    {
        $clone = clone $this;
        $clone->deleteAfterExistingInterval = $interval;
        $clone->deleteAfterExistingCondition = $condition;
        return $clone;
    }

    public function withBusinessHours(string $startTime, string $endTime): self
    {
        $clone = clone $this;
        $clone->businessHoursStart = $startTime;
        $clone->businessHoursEnd = $endTime;
        return $clone;
    }

    public function inTimezone(string $timezone): self
    {
        $clone = clone $this;
        $clone->timezone = $timezone;
        return $clone;
    }

    public function withTimezoneAwareScheduling(): self
    {
        $clone = clone $this;
        $clone->timezoneAwareScheduling = true;
        return $clone;
    }

    public function withTimezoneAwareBusinessHours(): self
    {
        $clone = clone $this;
        $clone->timezoneAwareBusinessHours = true;
        return $clone;
    }

    public function withTimezoneAwareDeletion(): self
    {
        $clone = clone $this;
        $clone->timezoneAwareDeletion = true;
        return $clone;
    }

    public function dueIn(string $duration): self
    {
        // Parse duration format like "30 minutes", "2 hours", "1 day"
        if (preg_match('/^(\d+)\s+(minute|hour|day)s?$/i', $duration, $matches)) {
            $amount = (int) $matches[1];
            $unit = strtolower($matches[2]);
            
            switch ($unit) {
                case 'minute':
                    return $this->dueMinutesAfter($amount);
                case 'hour':
                    return $this->dueHoursAfter($amount);
                case 'day':
                    return $this->dueDaysAfter($amount);
            }
        }
        
        // Fallback for common hardcoded cases
        if ($duration === '30 minutes') {
            return $this->dueMinutesAfter(30);
        } elseif ($duration === '2 hours') {
            return $this->dueHoursAfter(2);
        } elseif ($duration === '1 hour') {
            return $this->dueHoursAfter(1);
        } elseif ($duration === '1 day') {
            return $this->dueDaysAfter(1);
        }
        
        // Default fallback
        return $this->dueMinutesAfter(30);
    }

    public function createBeforeDue(string $duration): self
    {
        // Parse duration format like "30 minutes", "2 hours", "1 day" 
        if (preg_match('/^(\d+)\s+(minute|hour|day)s?$/i', $duration, $matches)) {
            $amount = (int) $matches[1];
            $unit = strtolower($matches[2]);
            
            switch ($unit) {
                case 'minute':
                    return $this->createMinutesBefore($amount);
                case 'hour':
                    return $this->createMinutesBefore($amount * 60);
                case 'day':
                    return $this->createDaysBefore($amount);
            }
        }
        
        // Fallback for common hardcoded cases
        if ($duration === '30 minutes') {
            return $this->createMinutesBefore(30);
        } elseif ($duration === '15 minutes') {
            return $this->createMinutesBefore(15);
        } elseif ($duration === '1 hour') {
            return $this->createMinutesBefore(60);
        }
        
        // Default fallback
        return $this->createMinutesBefore(30);
    }

    public function dueAfter(string $duration): self
    {
        // Alias for dueIn - same functionality
        return $this->dueIn($duration);
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

    public function getDeleteAfterDueInterval(): ?string
    {
        return $this->deleteAfterDueInterval;
    }

    public function getDeleteAfterDueCondition(): ?string
    {
        return $this->deleteAfterDueCondition;
    }

    public function getDeleteAfterExistingInterval(): ?string
    {
        return $this->deleteAfterExistingInterval;
    }

    public function getDeleteAfterExistingCondition(): ?string
    {
        return $this->deleteAfterExistingCondition;
    }

    public function getBusinessHoursStart(): ?string
    {
        return $this->businessHoursStart;
    }

    public function getBusinessHoursEnd(): ?string
    {
        return $this->businessHoursEnd;
    }

    public function hasTimezoneAwareScheduling(): bool
    {
        return $this->timezoneAwareScheduling;
    }

    public function hasTimezoneAwareBusinessHours(): bool
    {
        return $this->timezoneAwareBusinessHours;
    }

    public function hasTimezoneAwareDeletion(): bool
    {
        return $this->timezoneAwareDeletion;
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

        // Apply deletion rules
        $definition = $this->applyDeletionRules($definition);

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

    private function applyDeletionRules(Definition $definition): Definition
    {
        // Apply after due deletion rules first
        if ($this->deleteAfterDueInterval !== null) {
            $afterDueBy = $this->createAfterDueByFromInterval($this->deleteAfterDueInterval);
            
            $deletionRule = match ($this->deleteAfterDueCondition) {
                'complete' => $afterDueBy->andIsComplete(),
                'incomplete' => $afterDueBy->andIsIncomplete(),
                'either', null => $afterDueBy,
                default => $afterDueBy,
            };
            
            $definition = $definition->automaticallyDelete($deletionRule);
        }

        // Note: Multiple automaticallyDelete calls may override each other
        // If we need both rules, we might need to use a different approach
        if ($this->deleteAfterExistingInterval !== null) {
            $afterExistingFor = $this->createAfterExistingForFromInterval($this->deleteAfterExistingInterval);
            
            $deletionRule = match ($this->deleteAfterExistingCondition) {
                'complete' => $afterExistingFor->andIsComplete(),
                'incomplete' => $afterExistingFor->andIsIncomplete(),
                'either', null => $afterExistingFor,
                default => $afterExistingFor,
            };
            
            $definition = $definition->automaticallyDelete($deletionRule);
        }

        return $definition;
    }

    private function createAfterDueByFromInterval(string $interval): AfterDueBy
    {
        return match ($interval) {
            '1 hour' => AfterDueBy::oneHour(),
            '2 hours' => AfterDueBy::twoHours(),
            '3 hours' => AfterDueBy::threeHours(),
            '4 hours' => AfterDueBy::fourHours(),
            '6 hours' => AfterDueBy::sixHours(),
            '12 hours' => AfterDueBy::twelveHours(),
            '1 day' => AfterDueBy::oneDay(),
            '2 days' => AfterDueBy::twoDays(),
            '3 days' => AfterDueBy::threeDays(),
            '4 days' => AfterDueBy::fourDays(),
            '5 days' => AfterDueBy::fiveDays(),
            '6 days' => AfterDueBy::sixDays(),
            '7 days', '1 week' => AfterDueBy::oneWeek(),
            '2 weeks' => AfterDueBy::twoWeeks(),
            '3 weeks' => AfterDueBy::threeWeeks(),
            default => AfterDueBy::oneDay(), // Default fallback
        };
    }

    private function createAfterExistingForFromInterval(string $interval): AfterExistingFor
    {
        return match ($interval) {
            '1 hour' => AfterExistingFor::oneHour(),
            '2 hours' => AfterExistingFor::twoHours(),
            '3 hours' => AfterExistingFor::threeHours(),
            '4 hours' => AfterExistingFor::fourHours(),
            '6 hours' => AfterExistingFor::sixHours(),
            '12 hours' => AfterExistingFor::twelveHours(),
            '1 day' => AfterExistingFor::oneDay(),
            '2 days' => AfterExistingFor::twoDays(),
            '3 days' => AfterExistingFor::threeDays(),
            '4 days' => AfterExistingFor::fourDays(),
            '5 days' => AfterExistingFor::fiveDays(),
            '6 days' => AfterExistingFor::sixDays(),
            '7 days', '1 week' => AfterExistingFor::oneWeek(),
            '2 weeks' => AfterExistingFor::twoWeeks(),
            '3 weeks' => AfterExistingFor::threeWeeks(),
            default => AfterExistingFor::oneDay(), // Default fallback
        };
    }

    public function convertIntervalToSeconds(string $interval): int
    {
        return match ($interval) {
            '1 hour' => 3600,
            '2 hours' => 7200,
            '3 hours' => 10800,
            '4 hours' => 14400,
            '6 hours' => 21600,
            '12 hours' => 43200,
            '1 day' => 86400,
            '2 days' => 172800,
            '3 days' => 259200,
            '4 days' => 345600,
            '5 days' => 432000,
            '6 days' => 518400,
            '7 days', '1 week' => 604800,
            '2 weeks' => 1209600,
            '3 weeks' => 1814400,
            default => 86400, // Default to 1 day
        };
    }

    public function isValidCompletionState(string $state): bool
    {
        return in_array($state, ['complete', 'incomplete', 'either'], true);
    }

    public function assertDeletionRulesApply(Todo $todo): void
    {
        if ($this->deleteAfterDueInterval !== null) {
            $condition = $this->deleteAfterDueCondition ?? 'either';
            
            switch ($condition) {
                case 'complete':
                    Assert::assertNotNull(
                        $todo->automaticallyDeleteWhenCompleteAndAfterDueAt(),
                        'Expected todo to have deletion rule for complete state after due'
                    );
                    break;
                case 'incomplete':
                    Assert::assertNotNull(
                        $todo->automaticallyDeleteWhenIncompleteAndAfterDueAt(),
                        'Expected todo to have deletion rule for incomplete state after due'
                    );
                    break;
                case 'either':
                    $hasCompleteDeletion = $todo->automaticallyDeleteWhenCompleteAndAfterDueAt() !== null;
                    $hasIncompleteDeletion = $todo->automaticallyDeleteWhenIncompleteAndAfterDueAt() !== null;
                    
                    Assert::assertTrue(
                        $hasCompleteDeletion || $hasIncompleteDeletion,
                        'Expected todo to have deletion rule after due for either completion state'
                    );
                    break;
            }
        }

        if ($this->deleteAfterExistingInterval !== null) {
            $condition = $this->deleteAfterExistingCondition ?? 'either';
            
            switch ($condition) {
                case 'complete':
                    Assert::assertNotNull(
                        $todo->automaticallyDeleteWhenCompleteAndAfterExistingAt(),
                        'Expected todo to have deletion rule for complete state after existing'
                    );
                    break;
                case 'incomplete':
                    Assert::assertNotNull(
                        $todo->automaticallyDeleteWhenIncompleteAndAfterExistingAt(),
                        'Expected todo to have deletion rule for incomplete state after existing'
                    );
                    break;
                case 'either':
                    $hasCompleteDeletion = $todo->automaticallyDeleteWhenCompleteAndAfterExistingAt() !== null;
                    $hasIncompleteDeletion = $todo->automaticallyDeleteWhenIncompleteAndAfterExistingAt() !== null;
                    
                    Assert::assertTrue(
                        $hasCompleteDeletion || $hasIncompleteDeletion,
                        'Expected todo to have deletion rule after existing for either completion state'
                    );
                    break;
            }
        }
    }

    public function assertLifecycleProgression(CarbonInterface $createTime, CarbonInterface $dueTime, Definition $definition): void
    {
        $finalizedDefinition = $definition->finalize();
        
        // Test todo exists at create time
        $todoAtCreate = $finalizedDefinition->currentInstance($createTime);
        Assert::assertNotNull($todoAtCreate, 'Todo should exist at create time');
        
        // Test todo exists at due time
        $todoAtDue = $finalizedDefinition->currentInstance($dueTime);
        Assert::assertNotNull($todoAtDue, 'Todo should exist at due time');
        
        // Validate timing relationships
        if ($todoAtCreate->createAt() !== null && $todoAtCreate->dueAt() !== null) {
            Assert::assertLessThanOrEqual(
                $todoAtCreate->dueAt(),
                $todoAtCreate->createAt(),
                'Create time should be before or equal to due time'
            );
        }
        
        // Validate deletion rules are consistent
        if ($this->deleteAfterDueInterval !== null || $this->deleteAfterExistingInterval !== null) {
            $this->assertDeletionRulesApply($todoAtCreate);
        }
    }

    public function assertTodoMatches(?Todo $todo): void
    {
        Assert::assertNotNull($todo, 'Expected Todo object but got null');

        if ($this->name !== null) {
            Assert::assertEquals(
                $this->name,
                $todo->name(),
                "Expected todo name '{$this->name}' but got '{$todo->name()}'"
            );
        }

        if ($this->priority !== null) {
            $expectedPriorityValue = $this->convertPriorityToNumber($this->priority);
            $actualPriorityValue = $todo->priority();
            
            Assert::assertEquals(
                $expectedPriorityValue,
                $actualPriorityValue,
                "Expected todo priority '{$expectedPriorityValue}' ({$this->priority}) but got '{$actualPriorityValue}'"
            );
        }

        // Validate due time if we have schedule configuration
        if ($this->scheduleTime !== null && $this->scheduleType === 'daily') {
            $expectedTime = $this->scheduleTime;
            $actualTime = $todo->dueAt()?->format('H:i');
            
            Assert::assertEquals(
                $expectedTime,
                $actualTime,
                "Expected todo due time '{$expectedTime}' but got '{$actualTime}'"
            );
        }

        // Note: Timezone validation will be enhanced in future phases
        // Currently timezone configuration is stored but not fully integrated
        // with the Definition building process
    }

    public function assertDefinitionMatches(Definition $definition): void
    {
        $finalizedDefinition = $definition->finalize();
        
        // Get a test instance to validate the definition
        $testTime = $this->baseTime ?? Carbon::now();
        $todo = $finalizedDefinition->currentInstance($testTime);

        if ($todo !== null) {
            $this->assertTodoMatches($todo);
        } else {
            // If we can't get an instance at base time, try at schedule time
            if ($this->scheduleTime !== null) {
                $scheduleTestTime = Carbon::parse($testTime->format('Y-m-d') . ' ' . $this->scheduleTime);
                $todo = $finalizedDefinition->currentInstance($scheduleTestTime);
                
                if ($todo !== null) {
                    $this->assertTodoMatches($todo);
                } else {
                    Assert::fail('Could not create todo instance from definition for validation');
                }
            } else {
                Assert::fail('Could not create todo instance from definition for validation');
            }
        }
    }

    private function convertPriorityToNumber(string $priority): ?int
    {
        return match ($priority) {
            'high' => 4,
            'medium' => 3,
            'low' => 2,
            'none' => 1,
            'default' => null,
            default => null,
        };
    }

    // Boundary Condition Helper Methods

    public function crossesDayBoundary(CarbonInterface $startTime, CarbonInterface $endTime): bool
    {
        return $startTime->format('Y-m-d') !== $endTime->format('Y-m-d');
    }

    public function crossesMonthBoundary(CarbonInterface $startTime, CarbonInterface $endTime): bool
    {
        return $startTime->format('Y-m') !== $endTime->format('Y-m');
    }

    public function crossesYearBoundary(CarbonInterface $startTime, CarbonInterface $endTime): bool
    {
        return $startTime->format('Y') !== $endTime->format('Y');
    }

    public function crossesQuarterBoundary(CarbonInterface $startTime, CarbonInterface $endTime): bool
    {
        $startQuarter = (int) ceil($startTime->month / 3);
        $endQuarter = (int) ceil($endTime->month / 3);
        
        return $startTime->year !== $endTime->year || $startQuarter !== $endQuarter;
    }

    public function crossesWeekendBoundary(CarbonInterface $startTime, CarbonInterface $endTime): bool
    {
        $startIsWeekend = $startTime->isWeekend();
        $endIsWeekend = $endTime->isWeekend();
        
        return $startIsWeekend !== $endIsWeekend;
    }

    public function crossesBusinessHourBoundary(CarbonInterface $startTime, CarbonInterface $endTime): bool
    {
        // Skip weekend days - business hours don't apply
        if ($startTime->isWeekend() && $endTime->isWeekend()) {
            return false;
        }
        
        if ($this->businessHoursStart === null || $this->businessHoursEnd === null) {
            return false; // No business hours configured
        }
        
        $startTimeOnly = $startTime->format('H:i');
        $endTimeOnly = $endTime->format('H:i');
        
        $startInBusinessHours = $startTimeOnly >= $this->businessHoursStart && $startTimeOnly < $this->businessHoursEnd;
        $endInBusinessHours = $endTimeOnly >= $this->businessHoursStart && $endTimeOnly < $this->businessHoursEnd;
        
        return $startInBusinessHours !== $endInBusinessHours;
    }

    public function aroundDSTTransition(CarbonInterface $startTime, CarbonInterface $endTime): bool
    {
        // Only check for DST in timezones that actually have DST transitions
        $timezone = $startTime->timezone ?? $this->timezone ?? 'UTC';
        
        if (is_string($timezone)) {
            $timezone = new \DateTimeZone($timezone);
        }
        
        // Get DST transitions for the year(s) in question
        $startYear = $startTime->year;
        $endYear = $endTime->year;
        
        $transitions = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $yearTransitions = $timezone->getTransitions(
                mktime(0, 0, 0, 1, 1, $year),
                mktime(23, 59, 59, 12, 31, $year)
            );
            $transitions = array_merge($transitions, $yearTransitions);
        }
        
        // Check if the time range spans any DST transition
        foreach ($transitions as $transition) {
            $transitionTime = Carbon::createFromTimestamp($transition['ts']);
            
            // Check if transition falls between start and end times
            if ($transitionTime->between($startTime, $endTime)) {
                return true;
            }
        }
        
        return false;
    }

    public function isLeapYear(int $year): bool
    {
        return Carbon::create($year, 1, 1)->isLeapYear();
    }

    public function generateLeapYearScenario(int $year = null): self
    {
        $year = $year ?? Carbon::now()->year;
        
        if (!$this->isLeapYear($year)) {
            $year = $year + (4 - ($year % 4)); // Find next leap year
        }
        
        return $this->withName("Leap Year {$year} Scenario")
                   ->at("{$year}-02-29")
                   ->daily();
    }

    public function generateMonthBoundaryScenario(int $month = null, int $year = null): self
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;
        
        $lastDayOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->day;
        
        return $this->withName("Month Boundary {$year}-{$month} Scenario")
                   ->at("{$year}-{$month}-{$lastDayOfMonth} 23:30")
                   ->daily();
    }

    public function generateDSTTransitionScenario(string $timezone = 'America/New_York', bool $springForward = true): self
    {
        $tz = new \DateTimeZone($timezone);
        $year = Carbon::now()->year;
        
        $transitions = $tz->getTransitions(
            mktime(0, 0, 0, 1, 1, $year),
            mktime(23, 59, 59, 12, 31, $year)
        );
        
        $targetTransition = null;
        foreach ($transitions as $transition) {
            if ($transition['isdst'] === $springForward) {
                $targetTransition = $transition;
                break;
            }
        }
        
        if ($targetTransition === null) {
            // Fallback to standard DST dates
            $transitionDate = $springForward ? "{$year}-03-09 01:30:00" : "{$year}-11-02 01:30:00";
        } else {
            $transitionTime = Carbon::createFromTimestamp($targetTransition['ts']);
            $transitionDate = $transitionTime->subHour()->format('Y-m-d H:i:s');
        }
        
        $transitionType = $springForward ? 'Spring Forward' : 'Fall Back';
        
        return $this->withName("DST {$transitionType} Scenario")
                   ->inTimezone($timezone)
                   ->at($transitionDate)
                   ->daily();
    }

    public function generateYearBoundaryScenario(int $year = null): self
    {
        $year = $year ?? Carbon::now()->year;
        
        return $this->withName("Year Boundary {$year} Scenario")
                   ->at("{$year}-12-31 23:45")
                   ->daily();
    }

    public function generateQuarterBoundaryScenario(int $quarter = 1, int $year = null): self
    {
        $year = $year ?? Carbon::now()->year;
        
        $quarterEndMonths = [3, 6, 9, 12];
        $endMonth = $quarterEndMonths[$quarter - 1] ?? 3;
        
        $lastDayOfQuarter = Carbon::create($year, $endMonth, 1)->endOfMonth();
        
        return $this->withName("Q{$quarter} Boundary {$year} Scenario")
                   ->at($lastDayOfQuarter->format('Y-m-d') . ' 23:30')
                   ->daily();
    }

    // Timezone-Aware Helper Methods

    public function convertToTimezone(CarbonInterface $time, string $targetTimezone): CarbonInterface
    {
        return $time->copy()->setTimezone($targetTimezone);
    }

    public function safeConvertToTimezone(CarbonInterface $time, string $targetTimezone): ?CarbonInterface
    {
        try {
            return $this->convertToTimezone($time, $targetTimezone);
        } catch (\Exception $e) {
            return null; // Return null for invalid timezones
        }
    }

    public function isSameTimeAcrossTimezones(CarbonInterface $time1, CarbonInterface $time2): bool
    {
        return $time1->utc()->equalTo($time2->utc());
    }

    public function addTimezoneAwareHours(CarbonInterface $time, int $hours): CarbonInterface
    {
        return $time->copy()->addHours($hours);
    }

    public function isWithinBusinessHours(CarbonInterface $time): bool
    {
        if ($this->businessHoursStart === null || $this->businessHoursEnd === null) {
            return false; // No business hours configured
        }

        if ($time->isWeekend()) {
            return false; // Weekends are not business days
        }

        $timeOnly = $time->format('H:i');
        return $timeOnly >= $this->businessHoursStart && $timeOnly < $this->businessHoursEnd;
    }

    public function isBusinessHoursEquivalent(CarbonInterface $time1, CarbonInterface $time2): bool
    {
        if (!$this->hasTimezoneAwareBusinessHours()) {
            return $this->isWithinBusinessHours($time1) === $this->isWithinBusinessHours($time2);
        }

        // Convert both times to the scenario's timezone for comparison
        $tz = $this->timezone ?? 'UTC';
        $converted1 = $this->convertToTimezone($time1, $tz);
        $converted2 = $this->convertToTimezone($time2, $tz);

        return $this->isSameTimeAcrossTimezones($converted1, $converted2);
    }

    // Timezone-Aware Assertion Methods

    public function assertSameTimeAcrossTimezones(CarbonInterface $time1, CarbonInterface $time2): void
    {
        Assert::assertTrue(
            $this->isSameTimeAcrossTimezones($time1, $time2),
            sprintf(
                'Expected times to be equivalent across timezones. %s (%s) vs %s (%s)',
                $time1->toISOString(),
                $time1->timezoneName,
                $time2->toISOString(),
                $time2->timezoneName
            )
        );
    }

    public function assertTimezoneEquivalence(array $timezones): void
    {
        if (empty($timezones)) {
            return;
        }

        $firstTime = null;
        foreach ($timezones as $timezone => $time) {
            Assert::assertInstanceOf(CarbonInterface::class, $time, "Time for timezone {$timezone} must be a Carbon instance");
            
            if ($firstTime === null) {
                $firstTime = $time;
                continue;
            }

            $this->assertSameTimeAcrossTimezones($firstTime, $time);
        }
    }

    public function assertTimezoneConversion(CarbonInterface $sourceTime, string $targetTimezone, CarbonInterface $expectedTime): void
    {
        $converted = $this->convertToTimezone($sourceTime, $targetTimezone);
        
        Assert::assertEquals(
            $expectedTime->format('Y-m-d H:i:s'),
            $converted->format('Y-m-d H:i:s'),
            sprintf(
                'Timezone conversion failed. Expected %s in %s, got %s',
                $expectedTime->format('Y-m-d H:i:s'),
                $targetTimezone,
                $converted->format('Y-m-d H:i:s')
            )
        );
    }

    public function assertWithinBusinessHours(CarbonInterface $time, string $message = ''): void
    {
        Assert::assertTrue(
            $this->isWithinBusinessHours($time),
            $message ?: sprintf('Expected %s to be within business hours (%s-%s)', 
                $time->format('H:i'), 
                $this->businessHoursStart ?? 'undefined', 
                $this->businessHoursEnd ?? 'undefined'
            )
        );
    }

    public function assertOutsideBusinessHours(CarbonInterface $time, string $message = ''): void
    {
        Assert::assertFalse(
            $this->isWithinBusinessHours($time),
            $message ?: sprintf('Expected %s to be outside business hours (%s-%s)', 
                $time->format('H:i'), 
                $this->businessHoursStart ?? 'undefined', 
                $this->businessHoursEnd ?? 'undefined'
            )
        );
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