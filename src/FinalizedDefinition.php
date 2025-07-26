<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

use DateTimeInterface;
use DateTimeImmutable;

final class FinalizedDefinition
{
    public function __construct(
        private string $name,
        private Schedulish $create,
        private ?int $priority = null,
        private ?Schedulish $due = null,
        private ?string $description = null,
        private ?Time $automaticallyDeleteWhenCompleteAndAfterDueBy = null,
        private ?Time $automaticallyDeleteWhenIncompleteAndAfterDueBy = null,
        private ?Time $automaticallyDeleteWhenCompleteAndAfterExistingFor = null,
        private ?Time $automaticallyDeleteWhenIncompleteAndAfterExistingFor = null,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function priority(): ?int
    {
        return $this->priority;
    }

    public function shouldBeCreatedAt(DateTimeInterface|DateTimeImmutable|string $when): bool
    {
        $when = Utils::toCarbon($when);

        if ($this->create->isSchedule()) {
            $createAt = $this->create->schedule()->currentlyDueAt($when);
        } else {
            $createAt = $this->calculateCreateWhenDueAt($when);
        }

        return $createAt && Utils::equalToTheMinute($when, $createAt);
    }

    public function shouldBeDueAt(DateTimeInterface|DateTimeImmutable|string $when): bool
    {
        if (!$this->due) {
            return false;
        }

        $when = Utils::toCarbon($when);

        if ($this->due->isSchedule()) {
            $dueAt = $this->due->schedule()->currentlyDueAt($when);
        } else {
            $createAt = $when->clone()->subSeconds($this->due->time()->inSeconds());

            $dueAt = $this->calculateDueDateWhenCreatedAt($createAt);
        }

        return $dueAt && Utils::equalToTheMinute($when, $dueAt);
    }

    public function currentInstance(DateTimeInterface|DateTimeImmutable|string $when): Todo
    {
        $when = Utils::toCarbon($when);

        if ($this->create->isSchedule()) {
            $createAt = $this->create->schedule()->currentlyDueAt($when);
            if (!Utils::equalToTheMinute($when, $createAt)) {
                $createAt = null;
                $dueAt = null;
            } else {
                $dueAt = $this->calculateDueDateWhenCreatedAt($createAt);
            }
        } else {
            $createAt = $this->calculateCreateWhenDueAt($when);
            $dueAt = $this->due->schedule()->currentlyDueAt($createAt);
            if (!Utils::equalToTheMinute($when, $createAt)) {
                $createAt = null;
                $dueAt = null;
            }
        }

        $automaticallyDeleteWhenCompleteAndAfterDueAt = ($dueAt && $this->automaticallyDeleteWhenCompleteAndAfterDueBy)
            ? $this->calculateDateFromRelativeTime($dueAt, $this->automaticallyDeleteWhenCompleteAndAfterDueBy)
            : null;
        $automaticallyDeleteWhenIncompleteAndAfterDueAt = ($dueAt && $this->automaticallyDeleteWhenIncompleteAndAfterDueBy)
            ? $this->calculateDateFromRelativeTime($dueAt, $this->automaticallyDeleteWhenIncompleteAndAfterDueBy)
            : null;
        $automaticallyDeleteWhenCompleteAndAfterExistingAt = ($createAt && $this->automaticallyDeleteWhenCompleteAndAfterExistingFor)
            ? $this->calculateDateFromRelativeTime($createAt, $this->automaticallyDeleteWhenCompleteAndAfterExistingFor)
            : null;
        $automaticallyDeleteWhenIncompleteAndAfterExistingAt = ($createAt && $this->automaticallyDeleteWhenIncompleteAndAfterExistingFor)
            ? $this->calculateDateFromRelativeTime($createAt, $this->automaticallyDeleteWhenIncompleteAndAfterExistingFor)
            : null;

        return new Todo(
            $this->name,
            $createAt?->toDateTimeImmutable(),
            $this->priority,
            $dueAt?->toDateTimeImmutable(),
            $this->description,
            $automaticallyDeleteWhenCompleteAndAfterDueAt?->toDateTimeImmutable(),
            $automaticallyDeleteWhenIncompleteAndAfterDueAt?->toDateTimeImmutable(),
            $automaticallyDeleteWhenCompleteAndAfterExistingAt?->toDateTimeImmutable(),
            $automaticallyDeleteWhenIncompleteAndAfterExistingAt?->toDateTimeImmutable()
        );
    }

    public function nextInstance(DateTimeInterface|DateTimeImmutable|string $when): Todo
    {
        $when = Utils::toCarbon($when);

        if ($this->create->isSchedule()) {
            $createAt = $this->create->schedule()->currentlyDueAt($when);
            $dueAt = $this->calculateDueDateWhenCreatedAt($createAt);
        } else {
            $createAt = $this->calculateNextCreateWhenDueAt($when);
            $dueAt = $this->due->schedule()->currentlyDueAt($createAt);
        }

        $automaticallyDeleteWhenCompleteAndAfterDueAt = ($dueAt && $this->automaticallyDeleteWhenCompleteAndAfterDueBy)
            ? $this->calculateDateFromRelativeTime($dueAt, $this->automaticallyDeleteWhenCompleteAndAfterDueBy)
            : null;
        $automaticallyDeleteWhenIncompleteAndAfterDueAt = ($dueAt && $this->automaticallyDeleteWhenIncompleteAndAfterDueBy)
            ? $this->calculateDateFromRelativeTime($dueAt, $this->automaticallyDeleteWhenIncompleteAndAfterDueBy)
            : null;
        $automaticallyDeleteWhenCompleteAndAfterExistingAt = ($createAt && $this->automaticallyDeleteWhenCompleteAndAfterExistingFor)
            ? $this->calculateDateFromRelativeTime($createAt, $this->automaticallyDeleteWhenCompleteAndAfterExistingFor)
            : null;
        $automaticallyDeleteWhenIncompleteAndAfterExistingAt = ($createAt && $this->automaticallyDeleteWhenIncompleteAndAfterExistingFor)
            ? $this->calculateDateFromRelativeTime($createAt, $this->automaticallyDeleteWhenIncompleteAndAfterExistingFor)
            : null;

        return new Todo(
            $this->name,
            $createAt?->toDateTimeImmutable(),
            $this->priority,
            $dueAt?->toDateTimeImmutable(),
            $this->description,
            $automaticallyDeleteWhenCompleteAndAfterDueAt?->toDateTimeImmutable(),
            $automaticallyDeleteWhenIncompleteAndAfterDueAt?->toDateTimeImmutable(),
            $automaticallyDeleteWhenCompleteAndAfterExistingAt?->toDateTimeImmutable(),
            $automaticallyDeleteWhenIncompleteAndAfterExistingAt?->toDateTimeImmutable()
        );
    }

    public function calculateDateFromRelativeTime(Carbon|DateTimeInterface|string|null $when, Time $time): Carbon
    {
        $when = Utils::toCarbon($when)->clone();

        return $when->addSeconds($time->inSeconds());
    }

    public function calculateDueDateWhenCreatedAt(DateTimeInterface|DateTimeImmutable|string $when): ?Carbon
    {
        if (!$this->due) {
            return null;
        }

        $when = Utils::toCarbon($when)->clone();

        if ($this->due->isTime()) {
            $currentCreate = $this->create->schedule()->currentlyDueAt($when);
            if (!Utils::equalToTheMinute($when, $currentCreate)) {
                return null;
            }

            return $this->calculateDateFromRelativeTime($when, $this->due->time());
        }

        $currentDue = $this->due->schedule()->currentlyDueAt($when);

        return Utils::equalToTheMinute($when, $currentDue) ? $when : null;
    }

    public function calculateCreateWhenDueAt(DateTimeInterface|DateTimeImmutable|string $when): ?Carbon
    {
        $when = Utils::toCarbon($when)->clone();

        if ($this->create->isTime()) {
            $currentDue = $this->due?->schedule()->currentlyDueAt($when);
            $calculatedCreate = $this->calculateDateFromRelativeTime($currentDue, $this->create->time()->invert());

            if (!Utils::equalToTheMinute($when, $calculatedCreate)) {
                return null;
            }

            return $calculatedCreate;
        }

        $currentCreate = $this->create->schedule()->currentlyDueAt($when);

        // return $currentCreate;
        return Utils::equalToTheMinute($when, $currentCreate) ? $when : null;
    }

    public function calculateNextCreateWhenDueAt(DateTimeInterface|DateTimeImmutable|string $when): Carbon
    {
        $when = Utils::toCarbon($when)->clone();

        if ($this->create->isTime()) {
            $currentDue = $this->due?->schedule()->currentlyDueAt($when);
            $calculatedCreate = $this->calculateDateFromRelativeTime($currentDue, $this->create->time()->invert());

            return $calculatedCreate;
        }

        return $this->create->schedule()->currentlyDueAt($when);
    }
}
