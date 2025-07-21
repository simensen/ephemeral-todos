<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

final class Time
{
    public function __construct(
        private int $inSeconds,
    ) {
    }

    public function inSeconds(): int
    {
        return $this->inSeconds;
    }

    public function invert(): self
    {
        return new self($this->inSeconds * -1);
    }
}
