<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

trait CompletionAware
{
    private bool $applyWhenComplete = true;
    private bool $applyWhenIncomplete = true;

    public function appliesWhenComplete(): bool
    {
        return $this->applyWhenComplete;
    }

    public function appliesWhenIncomplete(): bool
    {
        return $this->applyWhenIncomplete;
    }

    public function appliesAlways(): bool
    {
        return $this->applyWhenComplete && $this->applyWhenIncomplete;
    }

    public function whetherCompletedOrNot(): self
    {
        $instance = clone($this);
        $instance->applyWhenComplete = true;
        $instance->applyWhenIncomplete = true;

        return $instance;
    }

    public function andIsIncomplete(): self
    {
        $instance = clone($this);
        $instance->applyWhenComplete = false;
        $instance->applyWhenIncomplete = true;

        return $instance;
    }

    public function andIsComplete(): self
    {
        $instance = clone($this);
        $instance->applyWhenComplete = true;
        $instance->applyWhenIncomplete = false;

        return $instance;
    }
}
