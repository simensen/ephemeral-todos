# Spec Tasks

These are the tasks to be completed for the spec detailed in @.agent-os/specs/2025-07-22-test-helpers-group-1/spec.md

> Created: 2025-07-22
> Status: Ready for Implementation

## Tasks

- [ ] 1. Create Carbon Time Management Trait
  - [ ] 1.1 Write tests for ManagesCarbonTime trait functionality
  - [ ] 1.2 Create ManagesCarbonTime trait with setUp/tearDown methods
  - [ ] 1.3 Add default test timestamp configuration
  - [ ] 1.4 Add ability to override default timestamp
  - [ ] 1.5 Verify all tests pass

- [ ] 2. Create DateTime Test Helpers
  - [ ] 2.1 Write tests for DateTime helper methods
  - [ ] 2.2 Create helper methods for common DateTimeImmutable patterns
  - [ ] 2.3 Add semantic naming for test dates (createTestDateTime, createDueDate, etc.)
  - [ ] 2.4 Support timezone-aware DateTime creation
  - [ ] 2.5 Verify all tests pass

- [ ] 3. Convert Relative Time Testing to Data Providers
  - [ ] 3.1 Write tests for relative time data provider
  - [ ] 3.2 Create data provider mapping time methods to expected values
  - [ ] 3.3 Convert AfterDueByTest relative time tests to use data provider
  - [ ] 3.4 Convert AfterExistingForTest relative time tests to use data provider
  - [ ] 3.5 Verify all converted tests pass

- [ ] 4. Standardize Time Travel Helper Usage
  - [ ] 4.1 Write tests to verify time travel helper conversion
  - [ ] 4.2 Replace Carbon::setTestNow() calls in BasicEnvironmentTest with TestCase helpers
  - [ ] 4.3 Replace Carbon::setTestNow() calls in CarbonTestHelpersTest with TestCase helpers
  - [ ] 4.4 Replace Carbon::setTestNow() calls in CronExpressionEdgeCasesTest with TestCase helpers
  - [ ] 4.5 Replace Carbon::setTestNow() calls in ManagesCronExpressionTest with TestCase helpers
  - [ ] 4.6 Replace Carbon::setTestNow() calls in ScheduleTest with TestCase helpers
  - [ ] 4.7 Verify all converted tests pass

- [ ] 5. Update Existing Tests to Use New Helpers
  - [ ] 5.1 Update TodosTest to use ManagesCarbonTime trait
  - [ ] 5.2 Update FinalizedDefinitionTest to use ManagesCarbonTime trait and DateTime helpers
  - [ ] 5.3 Update TestScenarioBuilderTest to use ManagesCarbonTime trait
  - [ ] 5.4 Update TodoTest and other files to use DateTime helpers where applicable
  - [ ] 5.5 Verify all updated tests pass

- [ ] 6. Documentation and Cleanup
  - [ ] 6.1 Add documentation for new test helpers in TestCase or separate docs
  - [ ] 6.2 Remove any obsolete test setup code
  - [ ] 6.3 Run full test suite to ensure no regressions
  - [ ] 6.4 Verify code formatting and style compliance