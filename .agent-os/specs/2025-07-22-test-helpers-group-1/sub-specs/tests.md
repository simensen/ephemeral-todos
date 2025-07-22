# Tests Specification

This is the tests coverage details for the spec detailed in @.agent-os/specs/2025-07-22-test-helpers-group-1/spec.md

> Created: 2025-07-22
> Version: 1.0.0

## Test Coverage

### Unit Tests

**ManagesCarbonTime Trait**
- Test trait properly sets Carbon test time in setUp
- Test trait properly resets Carbon state in tearDown  
- Test trait allows custom timestamp override
- Test trait works with TestCase inheritance
- Test trait prevents test pollution between test methods

**DateTime Test Helpers**
- Test each helper method creates correct DateTimeImmutable instances
- Test helper methods with different timezone parameters
- Test helper methods maintain immutability
- Test semantic naming matches expected date/time values

### Integration Tests

**Relative Time Data Provider Integration**
- Test data provider correctly maps all convenience methods to expected values
- Test parameterized test properly validates all time methods
- Test data provider is easily extensible for new methods
- Test converted tests maintain original assertion behavior

**Time Travel Helper Conversion**
- Test all converted time travel calls work identically to original Carbon calls
- Test no test behavior changes after conversion
- Test consistent time manipulation across test suite
- Test existing TestCase helpers work properly with converted tests

### Mocking Requirements

**Carbon Time Management**
- No external mocking required - uses Carbon's built-in test helpers
- May need to verify Carbon::setTestNow() calls during trait testing

**DateTime Creation**
- No mocking required for DateTimeImmutable creation
- May need timezone mocking for timezone-aware helper testing