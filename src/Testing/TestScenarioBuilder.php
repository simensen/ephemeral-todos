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