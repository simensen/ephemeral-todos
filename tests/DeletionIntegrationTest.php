<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\Definition;
use Simensen\EphemeralTodos\Schedule;
use Simensen\EphemeralTodos\Time;
use Simensen\EphemeralTodos\In;
use Simensen\EphemeralTodos\BeforeDueBy;
use Simensen\EphemeralTodos\Todo;

class DeletionIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow('2024-01-15 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function test_todo_with_after_due_by_deletion_rule()
    {
        $definition = Definition::define()
            ->withName('Complete project report')
            ->withHighPriority()
            ->due(In::twoHours())
            ->automaticallyDelete(AfterDueBy::oneDay());

        $finalizedDef = $definition->finalize();
        
        // Create a todo at due time (12:00)
        $todo = Todo::fromFinalizedDefinition($finalizedDef, Carbon::parse('2024-01-15 12:00:00'));
        
        // Todo should be deleted 1 day after due time (next day at 12:00)
        $expectedDeletionTime = Carbon::parse('2024-01-16 12:00:00');
        $this->assertEquals($expectedDeletionTime, $todo->automaticallyDeleteAt());
    }

    public function test_todo_with_after_existing_for_deletion_rule()
    {
        $definition = Definition::define()
            ->withName('Daily standup meeting')
            ->withMediumPriority()
            ->create(BeforeDueBy::oneHour())
            ->due(In::twoHours())
            ->automaticallyDelete(AfterExistingFor::oneDay());

        $finalizedDef = $definition->finalize();
        
        // Create a todo at its creation time (11:00)
        $todo = Todo::fromFinalizedDefinition($finalizedDef, Carbon::parse('2024-01-15 11:00:00'));
        
        // Todo should be deleted 1 day after creation time (next day at 11:00)
        $expectedDeletionTime = Carbon::parse('2024-01-16 11:00:00');
        $this->assertEquals($expectedDeletionTime, $todo->automaticallyDeleteAt());
    }

    public function test_completion_aware_deletion_scenarios()
    {
        $definition = Definition::define()
            ->withName('Review pull request')
            ->withHighPriority()
            ->due(In::fourHours())
            ->automaticallyDelete(AfterDueBy::oneDay()->andIsComplete());

        $finalizedDef = $definition->finalize();
        
        // Create todo at due time
        $todo = Todo::fromFinalizedDefinition($finalizedDef, Carbon::parse('2024-01-15 14:00:00'));
        
        // With andIsComplete(), the deletion rule should apply
        $deletionRule = $finalizedDef->automaticallyDelete();
        $this->assertTrue($deletionRule->appliesWhenComplete());
        $this->assertFalse($deletionRule->appliesWhenIncomplete());
        
        // Todo should be deleted 1 day after due time if completed
        $expectedDeletionTime = Carbon::parse('2024-01-16 14:00:00');
        $this->assertEquals($expectedDeletionTime, $todo->automaticallyDeleteAt());
    }

    public function test_complex_deletion_scenario_with_scheduling()
    {
        $definition = Definition::define()
            ->withName('Weekly team meeting')
            ->withMediumPriority()
            ->due(Schedule::weekly()->mondays()->at('09:00'))
            ->create(BeforeDueBy::oneHour())
            ->automaticallyDelete(AfterExistingFor::oneWeek()->andIsComplete());

        $finalizedDef = $definition->finalize();
        
        // Next Monday at 9:00 AM would be 2024-01-15 (it's already Monday)
        $nextScheduledTime = Carbon::parse('2024-01-15 09:00:00');
        
        // Todo creates 1 hour before (8:00 AM)
        $createTime = $nextScheduledTime->copy()->subHour();
        $todo = Todo::fromFinalizedDefinition($finalizedDef, $createTime);
        
        // Should delete 1 week after creation time (next Monday at 8:00 AM)
        $expectedDeletionTime = $createTime->copy()->addWeek();
        $this->assertEquals($expectedDeletionTime, $todo->automaticallyDeleteAt());
        
        // Verify the deletion rule applies only when complete
        $deletionRule = $finalizedDef->automaticallyDelete();
        $this->assertTrue($deletionRule->appliesWhenComplete());
        $this->assertFalse($deletionRule->appliesWhenIncomplete());
    }

    public function test_different_deletion_strategies_comparison()
    {
        $baseTime = Carbon::parse('2024-01-15 10:00:00');
        
        // After due by strategy
        $afterDueByDef = Definition::define()
            ->withName('Task 1')
            ->due(In::twoHours())
            ->automaticallyDelete(AfterDueBy::oneDay())
            ->finalize();
            
        $afterDueByTodo = Todo::fromFinalizedDefinition($afterDueByDef, $baseTime->copy()->addHours(2));
        
        // After existing for strategy
        $afterExistingForDef = Definition::define()
            ->withName('Task 2')
            ->due(In::twoHours())
            ->automaticallyDelete(AfterExistingFor::oneDay())
            ->finalize();
            
        $afterExistingForTodo = Todo::fromFinalizedDefinition($afterExistingForDef, $baseTime->copy()->addHours(2));
        
        // Both todos created at same time, but deletion times should be different
        $this->assertEquals(
            $baseTime->copy()->addHours(2)->addDay(), // Due time + 1 day
            $afterDueByTodo->automaticallyDeleteAt()
        );
        
        $this->assertEquals(
            $baseTime->copy()->addHours(2)->addDay(), // Creation time + 1 day (same in this case)
            $afterExistingForTodo->automaticallyDeleteAt()
        );
    }

    public function test_deletion_rule_with_different_time_durations()
    {
        $testCases = [
            ['duration' => AfterDueBy::fifteenMinutes(), 'expected' => 15 * 60],
            ['duration' => AfterDueBy::oneHour(), 'expected' => 60 * 60],
            ['duration' => AfterDueBy::fourHours(), 'expected' => 4 * 60 * 60],
            ['duration' => AfterDueBy::oneDay(), 'expected' => 24 * 60 * 60],
            ['duration' => AfterDueBy::oneWeek(), 'expected' => 7 * 24 * 60 * 60],
        ];

        $baseTime = Carbon::parse('2024-01-15 10:00:00');
        $dueTime = $baseTime->copy()->addHours(2);

        foreach ($testCases as $testCase) {
            $definition = Definition::define()
                ->withName('Test task')
                ->due(In::twoHours())
                ->automaticallyDelete($testCase['duration'])
                ->finalize();

            $todo = Todo::fromFinalizedDefinition($definition, $dueTime);
            
            $expectedDeletionTime = $dueTime->copy()->addSeconds($testCase['expected']);
            $this->assertEquals(
                $expectedDeletionTime,
                $todo->automaticallyDeleteAt(),
                "Failed for duration: {$testCase['expected']} seconds"
            );
        }
    }

    public function test_no_deletion_rule_results_in_null()
    {
        $definition = Definition::define()
            ->withName('Persistent task')
            ->withLowPriority()
            ->due(In::oneHour());
            
        $finalizedDef = $definition->finalize();
        $todo = Todo::fromFinalizedDefinition($finalizedDef, Carbon::now()->addHour());
        
        $this->assertNull($todo->automaticallyDeleteAt());
    }
}