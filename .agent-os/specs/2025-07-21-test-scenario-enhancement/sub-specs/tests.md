# Tests Specification

This is the tests coverage details for the spec detailed in @.agent-os/specs/2025-07-21-test-scenario-enhancement/spec.md

> Created: 2025-07-21
> Version: 1.0.0

## Test Coverage

### Unit Tests

**TestScenarioBuilder**
- Fluent method chaining maintains immutability
- Preset factory methods create correct configurations
- Time calculation methods produce accurate relative times
- Configuration reset and branching work correctly
- Invalid configuration throws appropriate exceptions

**TimeCalculationEngine**
- Base time configuration and retrieval
- Relative time calculations (before/after base time)
- Timezone conversion accuracy
- DST transition handling across different zones
- Edge cases like leap years and month boundaries

**AssertionFramework**
- Deep Todo object comparison including all properties
- Custom PHPUnit constraints work with PHPUnit integration
- Temporal assertions handle time precision correctly
- Collection assertions validate multiple todos simultaneously
- Error messages provide clear diagnostic information

**PresetTemplates**
- Template loading and parsing from configuration
- Parameter substitution works correctly
- Template inheritance and composition
- Version compatibility handling
- Invalid template configuration error handling

**BoundaryConditionHelpers**
- DST transition detection and setup
- Timezone boundary test generation
- Time precision edge case handling
- Leap year calculations
- Historical timezone data integration

**DeletionRuleManagement**
- Completion-aware deletion testing both paths
- Time-based deletion rule verification
- Complex lifecycle scenario validation
- Mocked time progression integration
- Multiple deletion strategy testing

**TimezoneAwareBuilding**
- Multi-timezone scenario construction
- Automatic timezone conversion validation
- Timezone-specific assertion accuracy
- DST transition testing across zones
- UTC vs local time handling

### Integration Tests

**Full Scenario Building Workflows**
- Complete fluent builder chains with assertions
- Preset template usage in real scenarios
- Complex multi-timezone scenario validation
- End-to-end deletion lifecycle testing

**Cross-Component Integration**
- Builder + assertion framework integration
- Time calculations + timezone handling
- Preset templates + boundary conditions
- All components working together seamlessly

**Backward Compatibility**
- Existing EphemeralTodoTestScenario still works
- Migration path from old to new API
- Interoperability between old and new approaches

### Feature Tests

**Real-World Scenario Validation**
- Daily meeting scheduling across timezones
- Weekly review with completion tracking
- Monthly cleanup with deletion rules
- Complex recurring patterns with edge cases

**Developer Experience Testing**
- Fluent API usability and discoverability
- Error message clarity and helpfulness
- Documentation examples work correctly
- Common use case patterns are intuitive

### Mocking Requirements

**Carbon Test Helpers**
- Mock current time for consistent testing
- Control time progression for deletion rule testing
- Simulate DST transitions and timezone changes

**PHPUnit Integration**
- Custom constraint integration with PHPUnit assertion framework
- Proper test isolation and cleanup
- Exception testing for invalid configurations

**Timezone Database Mocking**
- Mock historical timezone data for consistent testing
- Control DST transition dates for boundary testing
- Simulate different timezone rules for validation