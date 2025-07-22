# Tests Specification

This is the tests coverage details for the spec detailed in @.agent-os/specs/2025-07-21-scenario-variation-generators/spec.md

> Created: 2025-07-21
> Version: 1.0.0

## Test Coverage

### Unit Tests

**VariationGenerator**
- Test fluent API method chaining builds correct dimension configuration
- Test dimension value validation and type safety
- Test filter application during variation generation
- Test integration with TestScenarioBuilder for scenario application
- Test memory efficiency with large combination matrices
- Test generator yielding and iteration behavior

**Dimension**
- Test dimension creation with different value types (enum, range, custom)
- Test value validation and constraint checking
- Test dimension value formatting for test naming
- Test edge cases like empty value sets and invalid ranges

**DimensionCombination**
- Test combination creation from dimension values
- Test application to TestScenarioBuilder instances
- Test type conversion and validation during application
- Test immutability and clone behavior during application

**VariationFilter**
- Test predicate-based filtering with various filter functions
- Test named preset filters for common exclusion patterns
- Test filter composition and chaining
- Test performance optimization with early filtering

### Integration Tests

**VariationGenerator + TestScenarioBuilder**
- Test complete workflow from dimension specification to scenario generation
- Test that all generated scenarios are valid and properly configured
- Test that variation application doesn't break existing TestScenarioBuilder functionality
- Test complex multi-dimensional variations work correctly

**PHPUnit Data Provider Integration**
- Test data provider generation yields correct number of combinations
- Test generated test names are descriptive and unique
- Test that parameterized tests execute correctly with generated scenarios
- Test test isolation and failure handling across variations

**Preset Variation Sets**
- Test that preset combinations generate expected scenarios
- Test that preset exclusion rules work correctly
- Test that presets can be extended and customized
- Test documentation examples work as described

### Performance Tests

**Large Matrix Generation**
- Test memory usage remains reasonable with large dimension combinations
- Test generation time scales appropriately with matrix size
- Test filter application doesn't significantly impact performance
- Test generator lazy evaluation works correctly

### Edge Case Tests

**Boundary Conditions**
- Test empty dimension sets and single-value dimensions
- Test maximum practical combination sizes
- Test invalid dimension combinations and error handling
- Test filter edge cases like contradictory filters

**Integration Edge Cases**
- Test scenarios where TestScenarioBuilder cannot accept dimension values
- Test invalid combinations that should be filtered but aren't
- Test error propagation from scenario building to variation generation

## Mocking Requirements

**Time-Based Testing**
- Mock Carbon time for deterministic temporal dimension testing
- Mock timezone databases for consistent timezone dimension testing
- Mock scheduling calculations for repeatable time-based scenarios

**TestScenarioBuilder Mocking**
- Mock TestScenarioBuilder methods to test variation application in isolation
- Mock scenario generation to test filter effectiveness
- Mock PHPUnit data provider behavior for integration testing

**Performance Mocking**
- Mock large dimension sets for memory testing without actual large data
- Mock slow operations to test generator efficiency and lazy evaluation