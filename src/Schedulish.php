<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

final class Schedulish
{
    public function __construct(
        private Schedule|Time $schedulish,
    ) {
    }

    public function isSchedule(): bool
    {
        return $this->schedulish instanceof Schedule;
    }

    public function isTime(): bool
    {
        return $this->schedulish instanceof Time;
    }

    public function schedule(): Schedule
    {
        if (!$this->schedulish instanceof Schedule) {
            throw new \LogicException('Requested the Schedule associated with this Schedulish, but there is no Schedule associated with it.');
        }

        return $this->schedulish;
    }

    public function time(): Time
    {
        if (!$this->schedulish instanceof Time) {
            throw new \LogicException('Requested the Time associated with this Schedulish, but there is no Time associated with it.');
        }

        return $this->schedulish;
    }

    public function sameAs(mixed $other = null): bool
    {
        if ($other instanceof Time) {
            $other = new self($other);
        }

        if ($other instanceof Schedule) {
            $other = new self($other);
        }

        if (!$other instanceof self) {
            return false;
        }

        if ($this->isSchedule() && $other->isSchedule()) {
            return $this->schedule()->cronExpression() === $other->schedule()->cronExpression();
        }

        if ($this->isTime() && $other->isTime()) {
            return $this->time()->inSeconds() === $other->time()->inSeconds();
        }

        return false;
    }
}
