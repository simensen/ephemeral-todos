# Technical Specification

This is the technical specification for the spec detailed in @.agent-os/specs/2025-07-19-comprehensive-test-suite/spec.md

> Created: 2025-07-19
> Version: 1.0.0

## Technical Requirements

- PHPUnit 10.x compatible test framework setup with PHP 8.1+ requirements
- Test directory structure following PSR-4 autoloading standards matching src/ namespace
- Carbon test helpers for predictable time-based testing without system clock dependencies
- Mock strategies for external dependencies while preserving immutable object behavior
- Code coverage reporting configuration targeting 90%+ coverage for all implemented classes
- Test categorization separating unit tests from integration tests for different execution contexts
- Trait testing approach for shared behaviors (ManagesCronExpression, CompletionAware, etc.)
- Edge case coverage for boundary conditions like timezone handling and cron expression edge cases

## Approach Options

**Option A: Standard PHPUnit Setup with Carbon TestNow**
- Pros: Simple setup, leverages Carbon's built-in test helpers, familiar to PHP developers
- Cons: Global state changes with Carbon::setTestNow() could affect test isolation

**Option B: Dependency Injection with Clock Interface** (Selected)
- Pros: True isolation between tests, no global state, easier to test edge cases, follows modern testing practices
- Cons: Requires refactoring existing code to accept clock dependencies

**Option C: Time Traveler Library with PHPUnit**
- Pros: Powerful time manipulation, good test isolation
- Cons: Additional dependency, learning curve, may be overkill for current needs

**Rationale:** Option B provides the best long-term maintainability and test reliability. While it requires some refactoring of existing classes to accept time dependencies, it ensures complete test isolation and makes testing time-based logic much more predictable and reliable.

## External Dependencies

- **PHPUnit ^10.0** - Modern testing framework with improved assertions and better PHP 8.1+ support
- **Justification:** Industry standard for PHP testing with excellent documentation and tooling

- **nesbot/carbon** - Already in use for date/time manipulation, provides test helpers
- **Justification:** Already a dependency, provides Carbon::setTestNow() for time-based testing

- **Laravel Collections** - Already in use in Todos::nextInstances() method
- **Justification:** Already a dependency, needed for existing functionality

- **dragonmantank/cron-expression** - Likely already in use for cron expression parsing
- **Justification:** Required for Schedule functionality testing

## Test Organization Structure

```
tests/
├── Unit/
│   ├── TodoTest.php
│   ├── DefinitionTest.php
│   ├── FinalizedDefinitionTest.php
│   ├── TodosTest.php
│   ├── ScheduleTest.php
│   ├── TimeTest.php
│   ├── AfterDueByTest.php
│   ├── AfterExistingForTest.php
│   ├── UtilsTest.php
│   └── Traits/
│       ├── ManagesCronExpressionTest.php
│       └── CompletionAwareTest.php
├── Integration/
│   ├── TodoLifecycleTest.php
│   ├── SchedulingWorkflowTest.php
│   └── DeletionWorkflowTest.php
└── TestCase.php (base test class)
```

## Time-Based Testing Strategy

- Use Carbon::setTestNow() in setUp() and tearDown() methods for predictable time
- Create helper methods for advancing time and testing temporal calculations
- Test timezone handling with specific timezone instances
- Verify cron expression calculations at different points in time
- Test edge cases like daylight saving time transitions and leap years