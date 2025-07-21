<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

use Carbon\Carbon;
use DateTimeInterface;

final class Todos
{
    /** @var FinalizedDefinition[] */
    private array $todos = [];

    /**
     * @param Definition|FinalizedDefinition|callable(Definition):(Definition) $toBeScheduled
     */
    public function define(Definition|FinalizedDefinition|callable $toBeScheduled): void
    {
        if (is_callable($toBeScheduled)) {
            $toBeScheduled = $toBeScheduled(Definition::define());
        }

        if ($toBeScheduled instanceof Definition) {
            $toBeScheduled = $toBeScheduled->finalize();
        }

        $this->todos[] = $toBeScheduled;
    }

    /** @return FinalizedDefinition[] */
    public function readyToBeCreatedAt(Carbon|DateTimeInterface|string|null $when = null): array
    {
        return array_filter($this->todos, fn (FinalizedDefinition $todo) => $todo->shouldBeCreatedAt($when));
    }

    /** @return Todo[] */
    public function nextInstances(Carbon|DateTimeInterface|string|null $when = null): array
    {
        return collect($this->todos)->map(fn (FinalizedDefinition $todo) => $todo->nextInstance($when))->toArray();
    }
}
