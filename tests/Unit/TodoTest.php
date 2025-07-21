<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit;

use DateTimeImmutable;
use Simensen\EphemeralTodos\Tests\TestCase;
use Simensen\EphemeralTodos\Todo;

class TodoTest extends TestCase
{
    public function testConstructorWithRequiredParameters(): void
    {
        $createAt = new DateTimeImmutable('2025-01-19 12:00:00');
        $todo = new Todo('Test Task', $createAt, 1);

        $this->assertEquals('Test Task', $todo->name());
        $this->assertEquals($createAt, $todo->createAt());
        $this->assertEquals(1, $todo->priority());
        $this->assertNull($todo->dueAt());
        $this->assertNull($todo->description());
    }

    public function testConstructorWithAllParameters(): void
    {
        $createAt = new DateTimeImmutable('2025-01-19 12:00:00');
        $dueAt = new DateTimeImmutable('2025-01-19 15:00:00');
        $deleteComplete = new DateTimeImmutable('2025-01-20 12:00:00');
        $deleteIncomplete = new DateTimeImmutable('2025-01-21 12:00:00');
        $deleteCompleteExisting = new DateTimeImmutable('2025-01-22 12:00:00');
        $deleteIncompleteExisting = new DateTimeImmutable('2025-01-23 12:00:00');

        $todo = new Todo(
            'Complete Task',
            $createAt,
            2,
            $dueAt,
            'Task description',
            $deleteComplete,
            $deleteIncomplete,
            $deleteCompleteExisting,
            $deleteIncompleteExisting
        );

        $this->assertEquals('Complete Task', $todo->name());
        $this->assertEquals($createAt, $todo->createAt());
        $this->assertEquals(2, $todo->priority());
        $this->assertEquals($dueAt, $todo->dueAt());
        $this->assertEquals('Task description', $todo->description());
        $this->assertEquals($deleteComplete, $todo->automaticallyDeleteWhenCompleteAndAfterDueAt());
        $this->assertEquals($deleteIncomplete, $todo->automaticallyDeleteWhenIncompleteAndAfterDueAt());
        $this->assertEquals($deleteCompleteExisting, $todo->automaticallyDeleteWhenCompleteAndAfterExistingAt());
        $this->assertEquals($deleteIncompleteExisting, $todo->automaticallyDeleteWhenIncompleteAndAfterExistingAt());
    }

    public function testConstructorWithNullValues(): void
    {
        $todo = new Todo('Null Task', null, null);

        $this->assertEquals('Null Task', $todo->name());
        $this->assertNull($todo->createAt());
        $this->assertNull($todo->priority());
        $this->assertNull($todo->dueAt());
        $this->assertNull($todo->description());
    }

    public function testContentHashGeneratesConsistentHash(): void
    {
        $createAt = new DateTimeImmutable('2025-01-19 12:00:00');
        $dueAt = new DateTimeImmutable('2025-01-19 15:00:00');

        $todo1 = new Todo('Test Task', $createAt, 1, $dueAt, 'Description');
        $todo2 = new Todo('Test Task', $createAt, 1, $dueAt, 'Description');

        $this->assertEquals($todo1->contentHash(), $todo2->contentHash());
    }

    public function testContentHashDiffersForDifferentTodos(): void
    {
        $createAt = new DateTimeImmutable('2025-01-19 12:00:00');
        $dueAt = new DateTimeImmutable('2025-01-19 15:00:00');

        $todo1 = new Todo('Test Task 1', $createAt, 1, $dueAt, 'Description');
        $todo2 = new Todo('Test Task 2', $createAt, 1, $dueAt, 'Description');

        $this->assertNotEquals($todo1->contentHash(), $todo2->contentHash());
    }

    public function testContentHashBugFixDueDate(): void
    {
        $createAt = new DateTimeImmutable('2025-01-19 12:00:00');
        $dueAt = new DateTimeImmutable('2025-01-19 15:00:00');

        $todo = new Todo('Test Task', $createAt, 1, $dueAt);
        $hash = $todo->contentHash();

        // Decode the hash to verify it contains the correct due date
        $decoded = json_decode(base64_decode($hash), true);

        // The bug was using createAt instead of dueAt for the 'due' field
        // This test will fail until the bug is fixed
        $this->assertEquals($dueAt->format('c'), $decoded['due'], 'contentHash should use dueAt, not createAt for due field');
        $this->assertEquals($createAt->format('c'), $decoded['create']);
    }

    public function testContentHashWithNullDates(): void
    {
        $todo = new Todo('Test Task', null, 1, null);
        $hash = $todo->contentHash();

        $decoded = json_decode(base64_decode($hash), true);

        $this->assertNull($decoded['create']);
        $this->assertNull($decoded['due']);
    }

    public function testShouldEventuallyBeDeletedReturnsFalseWhenNoDeletionTimestamps(): void
    {
        $todo = new Todo('Test Task', $this->now(), 1);

        $this->assertFalse($todo->shouldEventuallyBeDeleted());
    }

    public function testShouldEventuallyBeDeletedReturnsTrueWithCompleteAfterDueTimestamp(): void
    {
        $deleteAt = new DateTimeImmutable('2025-01-20 12:00:00');
        $todo = new Todo(
            'Test Task',
            $this->now(),
            1,
            null,
            null,
            $deleteAt
        );

        $this->assertTrue($todo->shouldEventuallyBeDeleted());
    }

    public function testShouldEventuallyBeDeletedReturnsTrueWithIncompleteAfterDueTimestamp(): void
    {
        $deleteAt = new DateTimeImmutable('2025-01-20 12:00:00');
        $todo = new Todo(
            'Test Task',
            $this->now(),
            1,
            null,
            null,
            null,
            $deleteAt
        );

        $this->assertTrue($todo->shouldEventuallyBeDeleted());
    }

    public function testShouldEventuallyBeDeletedReturnsTrueWithCompleteAfterExistingTimestamp(): void
    {
        $deleteAt = new DateTimeImmutable('2025-01-20 12:00:00');
        $todo = new Todo(
            'Test Task',
            $this->now(),
            1,
            null,
            null,
            null,
            null,
            $deleteAt
        );

        $this->assertTrue($todo->shouldEventuallyBeDeleted());
    }

    public function testShouldEventuallyBeDeletedReturnsTrueWithIncompleteAfterExistingTimestamp(): void
    {
        $deleteAt = new DateTimeImmutable('2025-01-20 12:00:00');
        $todo = new Todo(
            'Test Task',
            $this->now(),
            1,
            null,
            null,
            null,
            null,
            null,
            $deleteAt
        );

        $this->assertTrue($todo->shouldEventuallyBeDeleted());
    }

    public function testAllGetterMethodsReturnCorrectValues(): void
    {
        $createAt = new DateTimeImmutable('2025-01-19 12:00:00');
        $dueAt = new DateTimeImmutable('2025-01-19 15:00:00');
        $deleteComplete = new DateTimeImmutable('2025-01-20 12:00:00');
        $deleteIncomplete = new DateTimeImmutable('2025-01-21 12:00:00');
        $deleteCompleteExisting = new DateTimeImmutable('2025-01-22 12:00:00');
        $deleteIncompleteExisting = new DateTimeImmutable('2025-01-23 12:00:00');

        $todo = new Todo(
            'Getter Test',
            $createAt,
            3,
            $dueAt,
            'Test description',
            $deleteComplete,
            $deleteIncomplete,
            $deleteCompleteExisting,
            $deleteIncompleteExisting
        );

        $this->assertEquals('Getter Test', $todo->name());
        $this->assertEquals($createAt, $todo->createAt());
        $this->assertEquals(3, $todo->priority());
        $this->assertEquals($dueAt, $todo->dueAt());
        $this->assertEquals('Test description', $todo->description());
        $this->assertEquals($deleteComplete, $todo->automaticallyDeleteWhenCompleteAndAfterDueAt());
        $this->assertEquals($deleteIncomplete, $todo->automaticallyDeleteWhenIncompleteAndAfterDueAt());
        $this->assertEquals($deleteCompleteExisting, $todo->automaticallyDeleteWhenCompleteAndAfterExistingAt());
        $this->assertEquals($deleteIncompleteExisting, $todo->automaticallyDeleteWhenIncompleteAndAfterExistingAt());
    }

    public function testTodoIsImmutable(): void
    {
        $createAt = new DateTimeImmutable('2025-01-19 12:00:00');
        $todo = new Todo('Immutable Test', $createAt, 1);

        // Verify all properties are readonly by checking there are no setter methods
        $reflection = new \ReflectionClass($todo);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $setterMethods = array_filter($methods, function ($method) {
            return str_starts_with($method->getName(), 'set');
        });

        $this->assertEmpty($setterMethods, 'Todo class should not have any setter methods to maintain immutability');
    }

    public function testContentHashIncludesAllRelevantFields(): void
    {
        $createAt = new DateTimeImmutable('2025-01-19 12:00:00');
        $dueAt = new DateTimeImmutable('2025-01-19 15:00:00');

        $todo = new Todo('Hash Test', $createAt, 2, $dueAt, 'Hash description');
        $hash = $todo->contentHash();

        $decoded = json_decode(base64_decode($hash), true);

        $this->assertEquals('Hash Test', $decoded['name']);
        $this->assertEquals('Hash description', $decoded['description']);
        $this->assertEquals(2, $decoded['priority']);
        $this->assertEquals($createAt->format('c'), $decoded['create']);
        // This will fail until the bug is fixed
        $this->assertEquals($dueAt->format('c'), $decoded['due']);
    }
}
