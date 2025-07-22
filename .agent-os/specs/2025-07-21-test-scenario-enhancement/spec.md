# Spec Requirements Document

> Spec: Test Scenario Enhancement
> Created: 2025-07-21
> Status: Planning

## Overview

Enhance EphemeralTodoTestScenario with comprehensive functionality including fluent builder patterns, automatic time calculations, assertion methods, preset templates, and specialized testing features. This enhancement will significantly improve the testing experience for the ephemeral-todos library and enable more thorough validation of complex scenarios.

## User Stories

### Developer Testing Story

As a PHP developer using the ephemeral-todos library, I want to easily create complex test scenarios with fluent methods and preset templates, so that I can thoroughly test my todo management implementations without writing repetitive setup code.

**Detailed Workflow:** Developers will use the enhanced EphemeralTodoTestScenario to quickly create test cases by chaining methods like `scenario()->dailyMeetings()->withTimezone('America/New_York')->assertTodoExists()` instead of manually setting up todos, times, and assertions. The builder will handle complex time calculations and provide comprehensive validation methods.

### Quality Assurance Story

As a library maintainer, I want comprehensive assertion methods that validate entire Todo objects and their relationships, so that I can catch edge cases and ensure the library behaves correctly across different scenarios.

**Detailed Workflow:** The enhanced scenario builder will provide methods like `assertTodoMatchesExpectation()`, `assertDeletionRulesApply()`, and `assertTimezoneHandling()` that validate not just simple properties but complex business logic and temporal relationships.

### Edge Case Testing Story

As a developer working with time-sensitive applications, I want boundary condition helpers and timezone-aware testing tools, so that I can validate my implementation works correctly across different time zones and edge cases like DST transitions.

**Detailed Workflow:** The builder will include methods like `aroundDSTTransition()`, `crossTimezoneComparison()`, and `boundaryTimeCalculations()` that automatically set up complex temporal scenarios and validate behavior across edge cases.

## Spec Scope

1. **Fluent Builder Pattern** - Implement chainable methods for scenario construction with common preset configurations
2. **Automatic Time Calculations** - Relative time handling that calculates dates/times based on configurable base times
3. **Comprehensive Assertion Methods** - Full Todo object validation including relationships and temporal logic
4. **Preset Template System** - Common scenario templates for typical use cases (daily meetings, weekly reviews, etc.)
5. **Boundary Condition Helpers** - Edge case testing tools for DST transitions, timezone boundaries, and temporal edge cases
6. **Deletion Rule Management** - Completion-aware deletion testing with complex lifecycle validation
7. **Timezone-Aware Building** - Multi-timezone scenario construction and validation tools

## Out of Scope

- Performance testing tools (future spec)
- GUI-based test builders (future spec)
- Integration with external testing frameworks beyond PHPUnit
- Storage-layer testing helpers (depends on Phase 2 storage implementation)

## Expected Deliverable

1. Enhanced testing infrastructure in `Simensen\EphemeralTodos\Testing` namespace under `src/Testing/` directory, making it available to library consumers
2. Enhanced EphemeralTodoTestScenario class with fluent builder pattern and all specified functionality
3. Comprehensive test coverage demonstrating all new features work correctly
4. Updated existing tests to use new builder methods where beneficial