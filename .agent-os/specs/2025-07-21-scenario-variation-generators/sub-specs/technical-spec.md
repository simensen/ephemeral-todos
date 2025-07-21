# Technical Specification

This is the technical specification for the spec detailed in @.agent-os/specs/2025-07-21-scenario-variation-generators/spec.md

> Created: 2025-07-21
> Version: 1.0.0

## Technical Requirements

### Dimensional Variation Engine
- Implement matrix multiplication algorithm for generating cartesian products of scenario dimensions
- Support unlimited number of dimensions with configurable value sets for each dimension
- Memory-efficient generation using PHP generators for large combination sets
- Type-safe dimension value validation and constraint checking

### Matrix Generation API
- Fluent interface methods for each supported dimension (priority(), completion(), timezone(), deleteAfter(), etc.)
- Method chaining that builds dimension configuration before generating variations
- Support for value ranges, specific value sets, and exclusion patterns
- Integration with existing TestScenarioBuilder for seamless scenario application

### Combination Filtering System
- Include/exclude filters based on dimension value combinations
- Predicate-based filtering using callable functions for complex rules
- Named filter presets for common exclusion patterns (e.g., "exclude invalid timezone/DST combinations")
- Performance optimization to apply filters during generation rather than post-processing

### PHPUnit Integration
- Data provider generation that yields TestScenarioBuilder instances for each variation
- Automatic test method naming based on dimension values for clear test identification
- Integration with PHPUnit's @dataProvider annotation for seamless test parameterization
- Test failure isolation ensuring one variation failure doesn't impact others

### Pre-defined Variation Sets
- Common dimension combinations like "basic coverage" (priority + completion), "temporal testing" (timezone + timing)
- Preset exclusion rules for known invalid combinations
- Expandable preset system allowing custom organization-specific presets
- Documentation and examples for each preset showing intended use cases

## Approach Options

**Option A: Generator-Based Lazy Evaluation** (Selected)
- Pros: Memory efficient for large combinations, streaming generation, composable with filters
- Cons: Cannot easily count total combinations before generation, more complex implementation

**Option B: Array-Based Eager Generation**
- Pros: Simple implementation, can count combinations, easier debugging
- Cons: Memory intensive for large matrices, less scalable, poor performance with filters

**Option C: Iterator Pattern with Caching**
- Pros: Balanced memory usage, reusable combinations, familiar pattern
- Cons: Complex caching logic, potential memory leaks, harder to implement filtering

**Rationale:** Option A provides the best scalability and memory efficiency, which is crucial when testing large combination matrices. PHP generators naturally support the streaming approach needed for efficient variation testing, and the composability with filtering makes it the most flexible approach.

## External Dependencies

**No new external dependencies required** - All functionality builds on existing infrastructure:
- **TestScenarioBuilder** - From the previous spec, provides the base scenario building capabilities
- **Laravel Collections** - Already used in library, provides collection operations for dimension management
- **PHPUnit** - Standard testing framework, data provider support for parameterized tests

## Implementation Architecture

### Core Classes (in Simensen\EphemeralTodos\Testing namespace)

**VariationGenerator**
- Main entry point with fluent API
- Manages dimension configuration and filter application
- Generates TestScenarioBuilder instances for each variation

**Dimension**
- Represents a single testable dimension (priority, completion, etc.)
- Contains possible values and validation rules
- Supports value ranges, exclusions, and custom value sets

**DimensionCombination**
- Represents a specific combination of dimension values
- Provides methods to apply the combination to a TestScenarioBuilder
- Handles type conversion and validation for dimension values

**VariationFilter**
- Implements filtering logic for including/excluding combinations
- Supports predicate-based filtering and named preset filters
- Optimized for application during generation rather than post-processing

### Integration Points

**TestScenarioBuilder Enhancement**
- Add methods to accept DimensionCombination objects and apply their values
- Ensure all existing builder methods are compatible with variation application
- Maintain immutability and fluent interface throughout variation application

**PHPUnit Data Provider Integration**
- Static methods that return generators yielding [TestScenarioBuilder, testName] pairs
- Automatic test naming based on dimension values for clear identification
- Support for custom test naming patterns and dimension value formatting

## Performance Considerations

- Use PHP generators for memory-efficient combination generation
- Apply filters during generation to avoid creating unnecessary combinations
- Optimize dimension ordering to fail early on invalid combinations
- Provide combination count estimates for test planning without full generation