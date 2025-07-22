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

    private function __construct()
    {
        $this->baseTime = Carbon::now();
    }

    public static function create(): self
    {
        return new self();
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

        // Handle create timing
        if ($this->createTime !== null && $this->dueTime !== null) {
            // Calculate relative timing for create before due
            $diffInMinutes = $this->dueTime->diffInMinutes($this->createTime);
            $definition = $definition->create(BeforeDueBy::minutes($diffInMinutes));
        } elseif ($this->createTime !== null) {
            // Create at specific time
            $definition = $definition->create(Schedule::create()->dailyAt($this->createTime->format('H:i')));
        }

        // Handle due timing
        if ($this->dueTime !== null) {
            if ($this->createTime === null) {
                // Due at specific time
                $definition = $definition->due(Schedule::create()->dailyAt($this->dueTime->format('H:i')));
            } else {
                // Due relative to create (already handled above)
                $definition = $definition->due(Schedule::create()->dailyAt($this->dueTime->format('H:i')));
            }
        }

        return $definition;
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