# Spec Tasks

These are the tasks to be completed for the spec detailed in @.agent-os/specs/2025-07-22-test-traits-group-2/spec.md

> Created: 2025-07-22
> Status: Ready for Implementation

## Tasks

- [ ] 1. Create Completion Awareness Test Trait
  - [ ] 1.1 Write tests for AssertsCompletionAwareness trait methods
  - [ ] 1.2 Create AssertsCompletionAwareness trait with assertHasCompletionAwareMethods method
  - [ ] 1.3 Add assertCanConfigureCompletionAwareness method to trait
  - [ ] 1.4 Add clear error messages for assertion failures
  - [ ] 1.5 Verify all trait tests pass

- [ ] 2. Create Immutability Test Trait
  - [ ] 2.1 Write tests for AssertsImmutability trait methods
  - [ ] 2.2 Create AssertsImmutability trait with assertMethodReturnsNewInstance method
  - [ ] 2.3 Add assertMultipleMethodsReturnNewInstances method for batch testing
  - [ ] 2.4 Add support for methods with parameters
  - [ ] 2.5 Verify all trait tests pass

- [ ] 3. Convert AfterDueByTest to Use New Traits
  - [ ] 3.1 Write tests to verify conversion maintains behavior
  - [ ] 3.2 Add trait imports to AfterDueByTest
  - [ ] 3.3 Replace completion awareness test methods with trait methods
  - [ ] 3.4 Replace immutability test methods with trait methods
  - [ ] 3.5 Verify all converted tests pass

- [ ] 4. Convert AfterExistingForTest to Use New Traits
  - [ ] 4.1 Write tests to verify conversion maintains behavior
  - [ ] 4.2 Add trait imports to AfterExistingForTest
  - [ ] 4.3 Replace completion awareness test methods with trait methods
  - [ ] 4.4 Replace immutability test methods with trait methods
  - [ ] 4.5 Verify all converted tests pass

- [ ] 5. Convert CompletionAwareTest to Use New Traits
  - [ ] 5.1 Write tests to verify conversion maintains behavior
  - [ ] 5.2 Add trait imports to CompletionAwareTest
  - [ ] 5.3 Replace completion awareness test methods with trait methods
  - [ ] 5.4 Replace immutability test methods with trait methods (if any)
  - [ ] 5.5 Verify all converted tests pass

- [ ] 6. Convert Other Immutability Tests to Use New Trait
  - [ ] 6.1 Convert DefinitionTest immutability tests to use trait
  - [ ] 6.2 Convert ManagesCronExpressionTest immutability tests to use trait
  - [ ] 6.3 Convert ScheduleTest immutability tests to use trait
  - [ ] 6.4 Convert any other test classes with immutability patterns
  - [ ] 6.5 Verify all converted tests pass

- [ ] 7. Documentation and Cleanup
  - [ ] 7.1 Add documentation for new test traits
  - [ ] 7.2 Remove obsolete test methods from converted classes
  - [ ] 7.3 Run full test suite to ensure no regressions
  - [ ] 7.4 Verify code formatting and style compliance