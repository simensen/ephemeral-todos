# Technical Specification

This is the technical specification for the spec detailed in @.agent-os/specs/2025-07-21-test-scenario-enhancement/spec.md

> Created: 2025-07-21
> Version: 1.0.0

## Technical Requirements

### Fluent Builder Implementation
- Implement method chaining using immutable clone-and-modify pattern consistent with library architecture
- Support preset scenarios via static factory methods (e.g., `dailyMeetings()`, `weeklyReview()`)
- Maintain type safety throughout the fluent chain
- Allow configuration reset and branching of scenario builders

### Time Calculation Engine
- Configurable base time that all relative calculations reference
- Support for relative offsets ("3 days from now", "2 hours before base time")
- Automatic handling of timezone conversions and DST transitions
- Carbon integration for consistent time manipulation

### Assertion Framework
- Deep Todo object comparison including all properties and deletion rules
- Temporal assertion methods that account for time precision and timezone differences
- Collection-based assertions for testing multiple todos simultaneously
- Custom PHPUnit constraint classes for reusable assertions

### Preset Template System
- JSON-based or PHP array configuration for common scenarios
- Template inheritance and composition capabilities
- Parameterizable templates with variable substitution
- Version compatibility for template evolution

### Boundary Condition Testing
- DST transition detection and automatic test case generation
- Timezone boundary testing across multiple zones
- Leap year and month boundary handling
- Time precision edge cases (microseconds, leap seconds)

### Deletion Rule Testing
- Completion-aware validation that tests both completed and incomplete deletion paths
- Time-based deletion rule verification with mocked time progression
- Complex lifecycle testing across multiple deletion strategies
- Integration with Carbon test helpers for time manipulation

### Timezone Management
- Multi-timezone scenario construction with automatic conversion
- Timezone-specific assertion methods that account for local vs UTC times
- DST transition testing across different timezone rules
- Time zone database integration for accurate historical data

## Approach Options

**Option A: Extend Current EphemeralTodoTestScenario**
- Pros: Maintains backward compatibility, leverages existing code, incremental improvement
- Cons: May become bloated, harder to redesign architecture, technical debt accumulation

**Option B: Create New TestScenarioBuilder Class in src/Testing/ Namespace** (Selected)
- Pros: Clean architecture, focused responsibility, better extensibility, easier testing, available to library consumers
- Cons: Breaking change, migration effort, dual maintenance initially

**Option C: Trait-Based Composition**
- Pros: Flexible mixing of capabilities, smaller focused traits, reusable components
- Cons: Complex inheritance chains, harder to understand flow, potential conflicts

**Rationale:** Option B provides the cleanest foundation for implementing all required features while maintaining the immutable, fluent interface patterns established in the library. The new class can leverage existing EphemeralTodoTestScenario components while providing a more focused and extensible architecture. Placing it in the `Simensen\EphemeralTodos\Testing` namespace makes it available to library consumers, enabling them to use the same powerful testing tools in their own test suites.

## External Dependencies

**No new external dependencies required** - All functionality can be implemented using existing library dependencies:
- **Carbon** - Already used for time manipulation throughout the library
- **PHPUnit** - Standard testing framework, constraint classes for custom assertions
- **Laravel Collections** - Already used in library for collection operations