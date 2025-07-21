<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use Carbon\Carbon;
use Simensen\EphemeralTodos\BeforeDueBy;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\FinalizedDefinition;
use Simensen\EphemeralTodos\In;
use Simensen\EphemeralTodos\Schedule;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\Testing\EphemeralTodoTestScenario;
use Simensen\EphemeralTodos\Todo;
use Simensen\EphemeralTodos\Todos;

class EphemeralTodoTest extends TestCase
{
    public static function createFinalizedDefinitionAt(Definition $definition, Carbon $when): FinalizedDefinition
    {
    }

    public static function createTodoAt(FinalizedDefinition $finalizedDefinition, Carbon $when): Todo
    {
    }

    /**
     * @test
     * @dataProvider provideData
     */
    #[DataProvider('provideData')]
    public function test_it_finalizes_as_expected(EphemeralTodoTestScenario $scenario): void
    {
        $todos = new Todos();
        $todos->define($scenario->definition);
        $todosReadyToBeCreated = $todos->readyToBeCreatedAt($scenario->when);

        if ($scenario->createsAt) {
            $this->assertCount(1, $todosReadyToBeCreated);
        } else {
            $this->assertCount(0, $todosReadyToBeCreated);
        }

        $finalizedDefintion = $scenario->definition->finalize();
        $todo = $finalizedDefintion->currentInstance($scenario->when);

        if ($scenario->createsAt) {
            $this->assertEquals($scenario->createsAt, $todo->createAt(), "Todo has expected create");
        } else {
            $this->assertNull($todo->createAt(), "Todo expected create to be null");
        }

        if ($scenario->dueAt) {
            $this->assertEquals($scenario->dueAt, $todo->dueAt(), "Todo has expected due");
        } else {
            $this->assertNull($todo->dueAt(), "Todo expected due to be null");
        }

        if ($scenario->createsAt) {
            $this->assertTrue(
                $finalizedDefintion->shouldBeCreatedAt($scenario->createsAt),
                'Should be created, but says no'
            );
        } else {
            $this->assertFalse($finalizedDefintion->shouldBeCreatedAt($scenario->createsAt), 'Should NOT be created, but says yes');
        }

        if ($scenario->dueAt) {
            $this->assertTrue($finalizedDefintion->shouldBeDueAt($scenario->dueAt));
        } else {
            $this->assertFalse($finalizedDefintion->shouldBeDueAt($scenario->dueAt));
        }

        $this->assertEquals(
            $scenario->automaticallyDeleteWhenCompleteAndAfterDueAt,
            $todo->automaticallyDeleteWhenCompleteAndAfterDueAt()
        );

        $this->assertEquals(
            $scenario->automaticallyDeleteWhenIncompleteAndAfterDueAt,
            $todo->automaticallyDeleteWhenIncompleteAndAfterDueAt()
        );

        $this->assertEquals(
            $scenario->automaticallyDeleteWhenCompleteAndAfterExistingAt,
            $todo->automaticallyDeleteWhenCompleteAndAfterExistingAt()
        );

        $this->assertEquals(
            $scenario->automaticallyDeleteWhenIncompleteAndAfterExistingAt,
            $todo->automaticallyDeleteWhenIncompleteAndAfterExistingAt()
        );
    }

    public static function provideData(): array
    {
        $tooEarlyMonday = Carbon::parse('Monday, January 24th 2022 at 8:29am');
        $earlyMonday = Carbon::parse('Monday, January 24th 2022 at 8:30am');
        $targetMonday = Carbon::parse('Monday, January 24th 2022 at 9:00am');
        $lateMonday = Carbon::parse('Monday, January 24th 2022 at 9:30am');
        $tooLateMonday = Carbon::parse('Monday, January 24th 2022 at 9:31am');

        $defintion = Definition::define()
            ->withName('test');

        return [
            'create only' => [
                new EphemeralTodoTestScenario(
                    $defintion->create(Schedule::create()->dailyAt('9:00')),
                    $targetMonday,
                    createsAt: $targetMonday,
                )
            ],

            'due only' => [
                new EphemeralTodoTestScenario(
                    $defintion->due(Schedule::create()->dailyAt('9:00')),
                    $targetMonday,
                    createsAt: $targetMonday,
                    dueAt: $targetMonday,
                )
            ],

            'create + due later' => [
                new EphemeralTodoTestScenario(
                    $defintion
                        ->create(Schedule::create()->dailyAt('9:00'))
                        ->due(In::thirtyMinutes()),
                    $targetMonday,
                    createsAt: $targetMonday,
                    dueAt: $lateMonday,
                )
            ],

            'due + create earlier' => [
                new EphemeralTodoTestScenario(
                    $defintion
                        ->due(Schedule::create()->dailyAt('9:00'))
                        ->create(BeforeDueBy::thirtyMinutes()),
                    $earlyMonday,
                    createsAt: $earlyMonday,
                    dueAt: $targetMonday,
                )
            ],
            'due + create earlier (too late)' => [
                new EphemeralTodoTestScenario(
                    $defintion
                        ->due(Schedule::create()->dailyAt('9:00'))
                        ->create(BeforeDueBy::thirtyMinutes()),
                    $tooLateMonday
                )
            ],
            'due + create earlier (too early)' => [
                new EphemeralTodoTestScenario(
                    $defintion
                        ->due(Schedule::create()->dailyAt('9:00'))
                        ->create(BeforeDueBy::thirtyMinutes()),
                    $tooEarlyMonday
                )
            ],
            'due + create earlier (close to midnight)' => [
                new EphemeralTodoTestScenario(
                    $defintion
                        ->due(Schedule::create()->dailyAt('00:10'))
                        ->create(BeforeDueBy::thirtyMinutes()),
                    Carbon::parse('2022-01-24 23:40:00'),
                    createsAt: Carbon::parse('2022-01-24 23:40:00'),
                    dueAt: Carbon::parse('2022-01-25 00:10:00')
                )
            ],
            'due + create earlier (after to midnight)' => [
                new EphemeralTodoTestScenario(
                    $defintion
                        ->due(Schedule::create()->dailyAt('00:41'))
                        ->create(BeforeDueBy::thirtyMinutes()),
                    Carbon::parse('2022-01-25 00:11:00'),
                    createsAt: Carbon::parse('2022-01-25 00:11:00'),
                    dueAt: Carbon::parse('2022-01-25 00:41:00')
                )
            ],
        ];
    }
}

// phpcs:disable
