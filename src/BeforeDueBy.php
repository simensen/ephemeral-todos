<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

final class BeforeDueBy
{
    use PrivateConstructor;
    use ManagesRelativeTime;

    public static function whenDue(): self
    {
        $instance = new self();
        $instance->timeInSeconds = 0;

        return $instance;
    }
}
