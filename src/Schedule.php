<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos;

final class Schedule
{
    use PrivateConstructor;
    use StaticCreateConstructor;
    use ManagesCronExpression;
}
