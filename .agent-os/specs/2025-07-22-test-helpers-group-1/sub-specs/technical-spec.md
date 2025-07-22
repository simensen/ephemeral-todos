# Technical Specification

This is the technical specification for the spec detailed in @.agent-os/specs/2025-07-22-test-helpers-group-1/spec.md

> Created: 2025-07-22
> Version: 1.0.0

## Technical Requirements

### Carbon Time Management Trait

- Create a trait `ManagesCarbonTime` with standardized setUp/tearDown methods
- Trait should set Carbon test time to a consistent default timestamp
- Trait should properly reset Carbon state in tearDown to prevent test pollution
- Must be compatible with existing TestCase inheritance structure
- Should allow override of default test timestamp when needed

### Relative Time Data Provider

- Convert individual test methods to data provider approach for time convenience methods
- Data provider should map method names to expected seconds values
- Must test all existing convenience methods: oneMinute, twoMinutes, fiveMinutes, etc.
- Preserve existing assertion logic while eliminating method duplication
- Should be easily extensible for new time convenience methods

### Time Travel Helper Standardization

- Identify all direct Carbon::setTestNow() calls in test files
- Replace with existing TestCase travelTo() and related helpers
- Ensure consistent behavior across all time manipulation in tests
- Maintain backward compatibility with existing test logic
- Document preferred time travel patterns for future tests

### DateTime Test Helpers

- Create helper methods for common DateTimeImmutable creation patterns
- Provide semantic methods like createTestDateTime(), createDueDate(), etc.
- Support timezone-aware DateTime creation when needed
- Should reduce repetitive new DateTimeImmutable() calls
- Must maintain immutability principles in helper design

## Approach Options

**Option A:** Create multiple specific traits for each concern
- Pros: Focused responsibilities, granular control, easy to test
- Cons: Multiple traits to import, potential naming conflicts

**Option B:** Single comprehensive test helper trait (Selected)
- Pros: Single import, cohesive time/date functionality, simpler to maintain
- Cons: Larger trait, potential for feature creep

**Rationale:** Option B provides better developer experience with a single import while keeping related time/date functionality together. The trait can be organized into logical sections internally.

## External Dependencies

No new external dependencies required. Implementation uses existing Carbon and PHP DateTimeImmutable functionality already present in the project.