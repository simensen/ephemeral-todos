<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

use DateTimeImmutable;

final class Todo
{
    public function __construct(
        private string $name,
        private ?DateTimeImmutable $createAt,
        private ?int $priority,
        private ?DateTimeImmutable $dueAt = null,
        private ?string $description = null,
        private ?DateTimeImmutable $automaticallyDeleteWhenCompleteAndAfterDueAt = null,
        private ?DateTimeImmutable $automaticallyDeleteWhenIncompleteAndAfterDueAt = null,
        private ?DateTimeImmutable $automaticallyDeleteWhenCompleteAndAfterExistingAt = null,
        private ?DateTimeImmutable $automaticallyDeleteWhenIncompleteAndAfterExistingAt = null,
    ) {
    }

    public function contentHash(): string
    {
        return base64_encode(json_encode([
            'name' => $this->name,
            'description' => $this->description,
            'priority' => $this->priority,
            'create' => $this->createAt?->format('c'),
            'due' => $this->dueAt?->format('c'),
        ]));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function priority(): ?int
    {
        return $this->priority;
    }

    public function createAt(): ?DateTimeImmutable
    {
        return $this->createAt;
    }

    public function dueAt(): ?DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function shouldEventuallyBeDeleted(): bool
    {
        return $this->automaticallyDeleteWhenCompleteAndAfterDueAt
            || $this->automaticallyDeleteWhenIncompleteAndAfterDueAt
            || $this->automaticallyDeleteWhenCompleteAndAfterExistingAt
            || $this->automaticallyDeleteWhenIncompleteAndAfterExistingAt;
    }

    public function automaticallyDeleteWhenCompleteAndAfterDueAt(): ?DateTimeImmutable
    {
        return $this->automaticallyDeleteWhenCompleteAndAfterDueAt;
    }

    public function automaticallyDeleteWhenIncompleteAndAfterDueAt(): ?DateTimeImmutable
    {
        return $this->automaticallyDeleteWhenIncompleteAndAfterDueAt;
    }

    public function automaticallyDeleteWhenCompleteAndAfterExistingAt(): ?DateTimeImmutable
    {
        return $this->automaticallyDeleteWhenCompleteAndAfterExistingAt;
    }

    public function automaticallyDeleteWhenIncompleteAndAfterExistingAt(): ?DateTimeImmutable
    {
        return $this->automaticallyDeleteWhenIncompleteAndAfterExistingAt;
    }
}
