# Spec Requirements Document

> Spec: Test Helpers Group 1 - Time and DateTime Management
> Created: 2025-07-22
> Status: Planning

## Overview

Create reusable test helper traits and methods to eliminate code duplication in time management, relative time testing, time travel helpers, and DateTime creation across the test suite. This will improve test maintainability and consistency while reducing repetitive code patterns.

## User Stories

### Carbon Time Management Simplification

As a test developer, I want to use a consistent time management pattern across all tests, so that I don't have to repeat Carbon::setTestNow() setup and teardown in every test class.

Tests currently manually set up and tear down Carbon test times with repetitive setUp() and tearDown() methods. A centralized trait will provide consistent time management behavior and reduce the risk of forgetting to reset Carbon state.

### Relative Time Testing Standardization  

As a test developer, I want to test relative time convenience methods using data providers, so that I can verify all time methods without duplicating assertion patterns.

Multiple test classes contain nearly identical tests for time convenience methods (oneMinute(), twoMinutes(), etc.) with the same assertion patterns. A parameterized approach will eliminate this duplication.

### Time Travel Helper Consistency

As a test developer, I want all tests to use the existing TestCase time travel helpers instead of direct Carbon calls, so that time manipulation is consistent across the test suite.

Some tests use the existing travelTo() helper while others directly call Carbon::setTestNow(), creating inconsistent time management patterns. Standardizing on the TestCase helpers will improve consistency.

### DateTime Test Helper Convenience

As a test developer, I want helper methods for creating common test DateTimeImmutable instances, so that I don't have to repeat the same date/time creation patterns in multiple test classes.

Tests frequently create DateTimeImmutable instances with similar patterns and timestamps. Helper methods will reduce this duplication and make test data creation more semantic.

## Spec Scope

1. **Carbon Time Management Trait** - Centralized setUp/tearDown for Carbon test time management
2. **Relative Time Data Provider** - Parameterized testing for time convenience methods  
3. **Time Travel Standardization** - Convert direct Carbon calls to use existing TestCase helpers
4. **DateTime Test Helpers** - Common methods for creating test DateTimeImmutable instances
5. **Documentation** - Usage examples and migration guidance for existing tests

## Out of Scope

- Changes to the core TestCase class structure
- Modification of production code time handling
- Complex timezone testing scenarios (handled separately)
- Performance optimization of time-based tests

## Expected Deliverable

1. Tests use consistent Carbon time management without repetitive setup/teardown
2. Relative time convenience methods tested via data providers instead of individual test methods
3. All time travel operations use TestCase helpers instead of direct Carbon calls
4. Common DateTime creation patterns available as helper methods
5. Existing test functionality preserved with improved code organization

## Spec Documentation

- Tasks: @.agent-os/specs/2025-07-22-test-helpers-group-1/tasks.md
- Technical Specification: @.agent-os/specs/2025-07-22-test-helpers-group-1/sub-specs/technical-spec.md
- Tests Specification: @.agent-os/specs/2025-07-22-test-helpers-group-1/sub-specs/tests.md