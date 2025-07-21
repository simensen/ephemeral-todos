<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

final class Definition
{
    private string $name;
    private string $description;
    private Schedulish $create;
    private ?int $priority;
    private Schedulish $due;

    private Time $automaticallyDeleteWhenCompleteAndAfterDueBy;
    private Time $automaticallyDeleteWhenIncompleteAndAfterDueBy;
    private Time $automaticallyDeleteWhenCompleteAndAfterExistingFor;
    private Time $automaticallyDeleteWhenIncompleteAndAfterExistingFor;

    private function __construct()
    {
    }

    public static function define(): self
    {
        return new self();
    }

    public function withName(string $name): self
    {
        $instance = clone $this;
        $instance->name = $name;

        return $instance;
    }

    public function withDescription(string $description): self
    {
        $instance = clone $this;
        $instance->description = $description;

        return $instance;
    }

    public function withHighPriority(): self
    {
        $instance = clone $this;
        $instance->priority = 4;

        return $instance;
    }

    public function withMediumPriority(): self
    {
        $instance = clone $this;
        $instance->priority = 3;

        return $instance;
    }

    public function withLowPriority(): self
    {
        $instance = clone $this;
        $instance->priority = 2;

        return $instance;
    }

    public function withNoPriority(): self
    {
        $instance = clone $this;
        $instance->priority = 1;

        return $instance;
    }

    public function withDefaultPriority(): self
    {
        $instance = clone $this;
        $instance->priority = null;

        return $instance;
    }

    public function create(Schedule|BeforeDueBy $create): self
    {
        $instance = clone $this;

        if ($create instanceof BeforeDueBy) {
            $create = $create->toTime();
        }

        $instance->create = new Schedulish($create);

        return $instance;
    }

    public function due(Schedule|In|callable $due): self
    {
        $instance = clone $this;

        if (is_callable($due)) {
            $due = $due(Schedule::create());
        }

        if ($due instanceof In) {
            $due = $due->toTime();
        }

        $instance->due = new Schedulish($due);

        return $instance;
    }

    public function automaticallyDelete(AfterDueBy|AfterExistingFor $delete): self
    {
        $instance = clone $this;

        $time = $delete->toTime();

        switch (get_class($delete)) {
            case AfterDueBy::class:
                if ($delete->appliesAlways()) {
                    $instance->automaticallyDeleteWhenCompleteAndAfterDueBy = $time;
                    $instance->automaticallyDeleteWhenIncompleteAndAfterDueBy = $time;
                } elseif ($delete->appliesWhenComplete()) {
                    $instance->automaticallyDeleteWhenCompleteAndAfterDueBy = $time;
                } elseif ($delete->appliesWhenIncomplete()) {
                    $instance->automaticallyDeleteWhenIncompleteAndAfterDueBy = $time;
                }

                break;

            case AfterExistingFor::class:
                if ($delete->appliesAlways()) {
                    $instance->automaticallyDeleteWhenCompleteAndAfterExistingFor = $time;
                    $instance->automaticallyDeleteWhenIncompleteAndAfterExistingFor = $time;
                } elseif ($delete->appliesWhenComplete()) {
                    $instance->automaticallyDeleteWhenCompleteAndAfterExistingFor = $time;
                } elseif ($delete->appliesWhenIncomplete()) {
                    $instance->automaticallyDeleteWhenIncompleteAndAfterExistingFor = $time;
                }

                break;

            default:
                throw new \InvalidArgumentException('Unsupported delete type: '.get_class($delete));
        }

        return $instance;
    }

    public function finalize(): FinalizedDefinition
    {
        $create = $this->create ?? null;
        $due = $this->due ?? null;

        if (!$create) {
            // If only a due date is specified, we will create
            // it right when it is due.
            $create = $due;
        }

        if (is_null($create)) {
            throw new \LogicException('You must define a `create` or `due` for a definition to be finalized.');
        }

        $arguments = array_filter([
            'name' => $this->name,
            'description' => $this->description ?? null,
            'create' => $create,
            'priority' => $this->priority ?? null,
            'due' => $due,
            'automaticallyDeleteWhenCompleteAndAfterDueBy' => $this->automaticallyDeleteWhenCompleteAndAfterDueBy ?? null,
            'automaticallyDeleteWhenIncompleteAndAfterDueBy' => $this->automaticallyDeleteWhenIncompleteAndAfterDueBy ?? null,
            'automaticallyDeleteWhenCompleteAndAfterExistingFor' => $this->automaticallyDeleteWhenCompleteAndAfterExistingFor ?? null,
            'automaticallyDeleteWhenIncompleteAndAfterExistingFor' => $this->automaticallyDeleteWhenIncompleteAndAfterExistingFor ?? null,
        ]);

        assert(array_key_exists('name', $arguments));

        return new FinalizedDefinition(...$arguments);
    }
}
