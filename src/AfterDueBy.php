<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

final class AfterDueBy
{
    use PrivateConstructor;
    use ManagesRelativeTime;
    use CompletionAware;
}
