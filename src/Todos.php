<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * @phpstan-type DefinitionFactory callable(Definition):(Definition|FinalizedDefinition)
 */
final class Todos
{
    /** @var FinalizedDefinition[] */
    private array $todos = [];

    /**
     * @param Definition|FinalizedDefinition|DefinitionFactory $toBeScheduled
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
    public function readyToBeCreatedAt(DateTimeInterface|DateTimeImmutable|string $when): array
    {
        return array_filter($this->todos, fn (FinalizedDefinition $todo) => $todo->shouldBeCreatedAt($when));
    }

    /** @return Todo[] */
    public function nextInstances(DateTimeInterface|DateTimeImmutable|string $when): array
    {
        return collect($this->todos)->map(fn (FinalizedDefinition $todo) => $todo->nextInstance($when))->toArray();
    }
}
