<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Testing;

use Carbon\Carbon;
use Simensen\EphemeralTodos\Definition;

class EphemeralTodoTestScenario
{
    public function __construct(
        public Definition $definition,
        public Carbon     $when,
        public ?Carbon    $createsAt = null,
        public ?Carbon    $dueAt = null,
        public ?Carbon    $automaticallyDeleteWhenCompleteAndAfterDueAt = null,
        public ?Carbon    $automaticallyDeleteWhenIncompleteAndAfterDueAt = null,
        public ?Carbon    $automaticallyDeleteWhenCompleteAndAfterExistingAt = null,
        public ?Carbon    $automaticallyDeleteWhenIncompleteAndAfterExistingAt = null,
    )
    {
    }
}
