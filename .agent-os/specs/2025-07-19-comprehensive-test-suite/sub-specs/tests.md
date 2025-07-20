# Tests Specification

This is the tests coverage details for the spec detailed in @.agent-os/specs/2025-07-19-comprehensive-test-suite/spec.md

> Created: 2025-07-19
> Version: 1.0.0

## Test Coverage

### Unit Tests

**Todo**
- Test constructor with all parameter combinations including optional deletion timestamps
- Test contentHash() method generates consistent hashes for identical todos
- Test contentHash() bug fix for due date (currently using createAt instead of dueAt)
- Test all getter methods return correct values
- Test shouldEventuallyBeDeleted() with various deletion timestamp combinations
- Test immutability of Todo objects

**Definition**
- Test fluent builder pattern with method chaining
- Test withName(), withDescription() methods preserve immutability  
- Test priority methods (withHighPriority, withMediumPriority, etc.) set correct values
- Test create() method with Schedule and BeforeDueBy parameters
- Test due() method with Schedule, In, and callable parameters
- Test automaticallyDelete() method with AfterDueBy and AfterExistingFor parameters
- Test finalize() method creates proper FinalizedDefinition
- Test finalize() throws LogicException when neither create nor due is defined
- Test edge cases in automaticallyDelete() switch statement logic

**FinalizedDefinition**
- Test shouldBeCreatedAt() with Schedule-based and Time-based create configurations
- Test shouldBeDueAt() with various due configurations
- Test currentInstance() returns null or Todo based on timing
- Test nextInstance() generates correct Todo instances
- Test temporal calculation methods (calculateDueDateWhenCreatedAt, etc.)
- Test automatic deletion timestamp calculations
- Test edge cases with null due dates and relative time calculations

**Todos**
- Test define() method with Definition, FinalizedDefinition, and callable parameters
- Test readyToBeCreatedAt() filters definitions correctly
- Test nextInstances() generates array of Todo instances
- Test collection behavior with multiple definitions

**Schedule**
- Test cron expression management and validation
- Test timezone handling
- Test all scheduling convenience methods (everyMinute, daily, weekly, etc.)
- Test filters and rejects with when() and skip() methods
- Test time interval methods (between, unlessBetween)
- Test isDue() and currentlyDueAt() calculations

**Time**
- Test constructor and inSeconds() method
- Test invert() method returns negated time
- Test immutability of Time objects

**AfterDueBy / AfterExistingFor**
- Test creation methods and time calculations
- Test completion-aware behavior (appliesAlways, appliesWhenComplete, appliesWhenIncomplete)
- Test toTime() method returns proper Time instances

**Utils**
- Test equalToTheMinute() with various Carbon/DateTime combinations
- Test toCarbon() conversion with different input types and timezones
- Test edge cases with null values and timezone handling

### Integration Tests

**Todo Lifecycle Workflow**
- Test complete workflow from Definition creation to Todo instance generation
- Test scheduling with cron expressions and relative times
- Test automatic deletion timestamp calculation in real scenarios
- Test priority inheritance from Definition to Todo

**Scheduling Integration**
- Test combination of Schedule and Time-based configurations
- Test due date calculations when create time is relative to due time
- Test multiple todo definitions with overlapping schedules

**Deletion Management Integration**
- Test completion-aware deletion rules in various scenarios
- Test combination of AfterDueBy and AfterExistingFor rules
- Test edge cases where deletion timestamps overlap or conflict

### Mocking Requirements

- **Carbon/DateTime:** Use Carbon::setTestNow() for predictable time-based testing
- **CronExpression:** Mock cron library responses for edge case testing
- **Timezone Calculations:** Test with specific timezone instances to verify cross-timezone behavior