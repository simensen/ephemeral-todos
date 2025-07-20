<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

trait StaticCreateConstructor
{
    public static function create(): self
    {
        return new self();
    }
}
