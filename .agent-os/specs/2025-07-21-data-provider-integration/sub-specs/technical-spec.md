# Technical Specification

This is the technical specification for the spec detailed in @.agent-os/specs/2025-07-21-data-provider-integration/spec.md

> Created: 2025-07-21
> Version: 1.0.0

## Technical Requirements

- **PHPUnit Integration:** Generate data provider arrays in PHPUnit format with proper naming and parameter structures
- **Validation Engine:** Comprehensive scenario validation with detailed error reporting and fix suggestions
- **Fluent API Extension:** Extend existing VariationGenerator with toDataProvider() and validation methods
- **Error Handling:** Structured exception hierarchy for different validation failure types
- **Performance:** Efficient validation that doesn't significantly slow down test setup
- **Developer Experience:** Clear, actionable error messages with context and suggested fixes

## Approach Options

**Option A:** Separate Classes for Each Component
- Pros: Clean separation of concerns, easier unit testing, flexible composition
- Cons: More classes to manage, potential complexity in integration

**Option B:** Extended VariationGenerator with Integrated Features (Selected)
- Pros: Seamless integration with existing API, natural fluent interface, fewer classes
- Cons: Larger class responsibility, potential for method proliferation

**Rationale:** Option B provides the most intuitive developer experience by extending the existing VariationGenerator API with validation and data provider generation methods. This maintains the fluent interface developers are already familiar with while adding the new capabilities.

## Core Components (in Simensen\EphemeralTodos\Testing namespace)

### DataProviderGenerator

**Purpose:** Convert scenario variations into PHPUnit data provider format

**Key Methods:**
- `toDataProvider(string $nameTemplate = null): array` - Convert variations to PHPUnit format
- `withNaming(callable $namingFunction): self` - Custom naming strategy for test cases
- `withParameters(array $additionalParams): self` - Add extra parameters to each variation

**Implementation:**
```php
class DataProviderGenerator
{
    public function generateFromVariations(array $variations, ?string $nameTemplate = null): array
    {
        $dataProvider = [];
        
        foreach ($variations as $index => $variation) {
            $testName = $this->generateTestName($variation, $nameTemplate, $index);
            $dataProvider[$testName] = [$variation];
        }
        
        return $dataProvider;
    }
    
    private function generateTestName(TestScenarioBuilder $scenario, ?string $template, int $index): string
    {
        // Intelligent naming based on scenario properties
        // Format: "priority_high_completed_timezone_est" or custom template
    }
}
```

### ScenarioValidator

**Purpose:** Validate scenario configurations and provide detailed error reporting

**Key Validation Rules:**
- Temporal consistency (creation time <= due time)
- Deletion rule compatibility with scenario timing
- Completion state logical consistency
- Timezone handling validation
- Priority and schedule compatibility

**Implementation:**
```php
class ScenarioValidator
{
    public function validate(TestScenarioBuilder $scenario): ValidationResult
    {
        $errors = [];
        
        $errors = array_merge($errors, $this->validateTemporal($scenario));
        $errors = array_merge($errors, $this->validateDeletionRules($scenario));
        $errors = array_merge($errors, $this->validateCompletion($scenario));
        $errors = array_merge($errors, $this->validateTimezone($scenario));
        
        return new ValidationResult($errors);
    }
    
    private function validateTemporal(TestScenarioBuilder $scenario): array
    {
        // Validate temporal relationships and constraints
    }
}
```

### VariationGenerator Extensions

**New Methods:**
- `toDataProvider(string $nameTemplate = null): array` - Generate PHPUnit data provider
- `validate(): ValidationResult` - Validate all generated variations
- `withValidation(bool $enabled = true): self` - Enable/disable automatic validation
- `explainConfiguration(): string` - Detailed explanation of current configuration

## Validation Framework

### Validation Rules

**Temporal Validation:**
- Creation time must be before or equal to due time
- Deletion timestamps must be logically consistent with todo lifecycle
- Relative time calculations must result in valid dates
- Timezone transitions must be handled correctly

**Completion Validation:**
- Completion status must be compatible with deletion rules
- Completion-aware deletion rules must have valid completion scenarios
- Completion times must be within valid ranges

**Configuration Validation:**
- Priority levels must be valid enum values
- Schedule expressions must be valid cron syntax
- Time zones must be valid PHP timezone identifiers
- Deletion strategies must be compatible with scenario timing

### Error Message Format

```php
class ValidationError
{
    public string $code;           // ERROR_TEMPORAL_INCONSISTENT
    public string $message;        // Human-readable description
    public string $suggestion;     // How to fix it
    public array $context;         // Relevant scenario data
    public ?string $documentation; // Link to relevant docs
}
```

## Integration Points

### With TestScenarioBuilder

- Extend builder with validation methods
- Add automatic validation option
- Integrate with fluent API seamlessly

### With VariationGenerator

- Add data provider generation capability
- Include validation in variation generation
- Maintain existing API compatibility

### With PHPUnit

- Generate standard PHPUnit data provider format
- Support custom naming conventions
- Handle parameter passing correctly

## External Dependencies

**None Required** - This spec builds entirely on existing infrastructure from previous specs and standard PHP/PHPUnit features.

## Performance Considerations

- Validation caching for repeated scenario configurations
- Lazy validation to avoid unnecessary computation
- Efficient data provider generation for large variation sets
- Memory-conscious handling of large scenario matrices