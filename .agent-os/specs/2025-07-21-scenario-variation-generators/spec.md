# Spec Requirements Document

> Spec: Scenario Variation Generators
> Created: 2025-07-21
> Status: Planning

## Overview

Implement scenario variation generators that build on the enhanced TestScenarioBuilder to automatically generate comprehensive test variations and combinations, reducing test duplication while ensuring thorough coverage across different scenario dimensions including completion states, priorities, timings, and deletion strategies.

## User Stories

### Test Coverage Optimization Story

As a PHP developer writing tests for ephemeral todos, I want to automatically generate variations of test scenarios across different dimensions (completion states, priorities, timings), so that I can achieve comprehensive test coverage without manually writing dozens of similar test cases.

**Detailed Workflow:** Developers will use variation generators like `VariationGenerator::priority()->completion()->timezone()` to automatically create test matrices that cover all combinations of priority levels, completion states, and timezone configurations. This ensures no edge case combinations are missed while dramatically reducing test code duplication.

### Dimensional Testing Story

As a library maintainer, I want to systematically test scenario combinations across multiple dimensions (temporal, priority, completion, deletion rules), so that I can identify interaction bugs between different feature combinations that might be missed in isolated testing.

**Detailed Workflow:** The variation generator will create cartesian products of scenario dimensions, automatically applying each combination to base test scenarios and validating that all combinations work correctly. This reveals bugs that only appear when specific features interact.

### Regression Prevention Story

As a developer maintaining ephemeral todo implementations, I want automated generation of edge case combinations that have historically caused issues, so that I can prevent regressions when making changes to the system.

**Detailed Workflow:** The generator will include predefined "problematic combination" sets that focus on historically challenging scenarios like timezone changes during DST combined with completion-aware deletion rules, ensuring these specific combinations are always tested.

## Spec Scope

1. **Dimensional Variation Engine** - Generate cartesian products across scenario dimensions like priority, completion, timing, and deletion rules
2. **Matrix Generation API** - Fluent interface for specifying which dimensions to vary and their value ranges
3. **Combination Filtering** - Include/exclude specific combinations to avoid invalid or irrelevant test cases
4. **Parametric Test Integration** - Generate PHPUnit data providers for systematic variation testing
5. **Pre-defined Variation Sets** - Common variation patterns for typical testing needs

## Out of Scope

- Performance optimization of test execution (handled by PHPUnit parallel execution)
- Visual test result reporting (future enhancement)
- Integration with CI/CD specific variation selection
- Dynamic variation generation based on code coverage analysis

## Expected Deliverable

1. VariationGenerator class in `Simensen\EphemeralTodos\Testing` namespace with fluent API for specifying test dimensions and generating scenario combinations
2. Integration with existing TestScenarioBuilder to apply variations to base scenarios
3. Comprehensive test coverage demonstrating the variation generator works correctly across all supported dimensions

## Spec Documentation

- Tasks: @.agent-os/specs/2025-07-21-scenario-variation-generators/tasks.md
- Technical Specification: @.agent-os/specs/2025-07-21-scenario-variation-generators/sub-specs/technical-spec.md
- Tests Specification: @.agent-os/specs/2025-07-21-scenario-variation-generators/sub-specs/tests.md