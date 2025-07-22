# Spec Tasks

These are the tasks to be completed for the spec detailed in @.agent-os/specs/2025-07-21-test-scenario-enhancement/spec.md

> Created: 2025-07-21
> Status: Ready for Implementation

## Tasks

- [x] 1. Phase 1: Core Testing Infrastructure
  - [x] 1.1 Write tests for TestScenarioBuilder class architecture
  - [x] 1.2 Create TestScenarioBuilder class in src/Testing/ namespace with fluent builder pattern
  - [x] 1.3 Implement basic time calculation engine with Carbon integration
  - [x] 1.4 Add immutable clone-and-modify pattern for method chaining
  - [x] 1.5 Verify all tests pass

- [ ] 2. Phase 2: Fluent Builder Pattern & Preset Templates
  - [ ] 2.1 Write tests for preset template system and static factory methods
  - [ ] 2.2 Implement preset scenario factory methods (dailyMeetings, weeklyReview, etc.)
  - [ ] 2.3 Add fluent configuration methods for common scenario properties
  - [ ] 2.4 Deploy enhancement to EphemeralTodoTest.php and reduce test verbosity
  - [ ] 2.5 Verify all tests pass

- [ ] 3. Phase 3: Automatic Time Calculations
  - [ ] 3.1 Write tests for relative time calculation engine
  - [ ] 3.2 Implement configurable base time with relative offset support
  - [ ] 3.3 Add automatic timestamp calculation for create/due/delete times
  - [ ] 3.4 Deploy to ComprehensiveEdgeCaseTest.php and TodoLifecycleIntegrationTest.php
  - [ ] 3.5 Verify all tests pass

- [ ] 4. Phase 4: Comprehensive Assertion Methods
  - [ ] 4.1 Write tests for deep Todo object validation and assertion framework
  - [ ] 4.2 Implement assertTodoMatches() and assertDefinitionMatches() methods
  - [ ] 4.3 Create custom PHPUnit constraint classes for reusable assertions
  - [ ] 4.4 Deploy assertion methods to DeletionIntegrationTest.php and reduce assertion boilerplate
  - [ ] 4.5 Verify all tests pass

- [ ] 5. Phase 5: Deletion Rule Management
  - [ ] 5.1 Write tests for completion-aware deletion testing
  - [ ] 5.2 Implement deletion rule configuration with completion awareness
  - [ ] 5.3 Add lifecycle validation methods for complex deletion scenarios
  - [ ] 5.4 Deploy to CompletionAwareTest.php and AfterDueByTest.php
  - [ ] 5.5 Verify all tests pass

- [ ] 6. Phase 6: Boundary Condition Helpers
  - [ ] 6.1 Write tests for DST transitions and timezone boundary testing
  - [ ] 6.2 Implement boundary condition helper methods (crossesDayBoundary, aroundDSTTransition)
  - [ ] 6.3 Add edge case scenario generators for leap years and month boundaries
  - [ ] 6.4 Deploy to BoundaryConditionTest.php and TimezoneHandlingTest.php
  - [ ] 6.5 Verify all tests pass

- [ ] 7. Phase 7: Timezone-Aware Building
  - [ ] 7.1 Write tests for multi-timezone scenario construction
  - [ ] 7.2 Implement timezone-aware builder methods with automatic conversion
  - [ ] 7.3 Add timezone-specific assertion methods
  - [ ] 7.4 Deploy timezone enhancements across relevant tests
  - [ ] 7.5 Verify all tests pass

- [ ] 8. Phase 8: Integration & Legacy Compatibility
  - [ ] 8.1 Write tests for backward compatibility with existing EphemeralTodoTestScenario
  - [ ] 8.2 Update existing EphemeralTodoTestScenario to leverage new TestScenarioBuilder
  - [ ] 8.3 Migrate remaining complex tests to use new builder pattern
  - [ ] 8.4 Add comprehensive integration tests for all features working together
  - [ ] 8.5 Verify all tests pass and no regressions introduced