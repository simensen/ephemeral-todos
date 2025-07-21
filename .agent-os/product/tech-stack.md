# Technical Stack

> Last Updated: 2025-01-19
> Version: 1.0.0

## Core Technologies

### Application Framework
- **Framework:** PHP Library (no framework dependency)
- **Version:** PHP 8.1+
- **Language:** PHP with strict typing

### Dependencies
- **Primary:** Carbon for date/time manipulation
- **Secondary:** Laravel Collections (used in Todos::nextInstances())
- **Version:** Compatible with modern PHP ecosystem

## Design Patterns

### Architecture Style
- **Pattern:** Domain-driven design with fluent interfaces
- **Approach:** Immutable objects with method chaining
- **Structure:** Trait-based composition for shared behaviors

### Code Organization
- **Namespace:** Simensen\EphemeralTodos
- **Conventions:** PascalCase classes, camelCase methods, strict typing
- **Immutability:** Clone-and-modify pattern for state changes

## Key Components

### Core Domain Objects
- **Todo:** Immutable value object representing a todo instance
- **Definition:** Fluent builder for todo specifications
- **FinalizedDefinition:** Compiled definition ready for execution
- **Todos:** Collection manager for multiple todo definitions

### Scheduling System
- **Schedule:** Cron expression wrapper for recurring events
- **Time:** Relative time duration handling
- **Schedulish:** Union type for Schedule or Time objects

### Deletion Management
- **AfterDueBy:** Time-based deletion after due date
- **AfterExistingFor:** Duration-based deletion after creation
- **CompletionAware:** Interface for completion-dependent behavior

## Infrastructure

### Package Distribution
- **Method:** Composer package
- **Namespace:** Simensen namespace (established PHP ecosystem presence)
- **Dependencies:** Minimal external dependencies for library adoption

### Testing Strategy
- **Framework:** PHPUnit (likely, standard for PHP libraries)
- **Coverage:** Unit tests for domain logic, integration tests for scheduling
- **Mocking:** Time-based testing with Carbon test helpers

### Development Tools
- **Standards:** PHP-CS-Fixer for code formatting
- **Analysis:** PHPStan for static analysis
- **Testing:** PHPUnit for testing
- **CI/CD:** GitHub Actions for automated testing

#### PHP-CS-Fixer for Code Formatting

**PHP-CS-Fixer** is installed using Phive and `nix-direnv` (via `.envrc` and `flake.nix`) ensures `php-cs-fixer` is available on `$PATH`, so any ad-hoc usage of `php-cs-fixer` can just be run as `php-cs-fixer` without any additional path prefixes (`./tools` or `./vendor`).

To **format** code, use the Composer script:

```bash
composer format:check
```

When **checking** code formatting, use the Composer script:

```bash
composer format:check
```

#### PHPStan for Static Analysis

**PHPStan** is installed using Phive and `nix-direnv` (via `.envrc` and `flake.nix`) ensures `phpstan` is available on `$PATH`, so any ad-hoc usage of `phpstan` can just be run as `phpstan` without any additional path prefixes (`./tools` or `./vendor`).

To **analyse** code, use the Composer script:

```bash
composer analyse
```

#### PHPUnit for Testing

**PHPUnit** is installed using Phive and `nix-direnv` (via `.envrc` and `flake.nix`) ensures `phpunit` is available on `$PATH`, so any ad-hoc usage of `phpunit` can just be run as `phpunit` without any additional path prefixes (`./tools` or `./vendor`).

## Deployment

### Distribution
- **Primary:** Packagist (Composer repository)
- **Versioning:** Semantic versioning
- **Documentation:** README with usage examples

### Integration
- **Framework Agnostic:** Works with Laravel, Symfony, or plain PHP
- **Database Neutral:** Library handles logic, storage implementation left to consumer
- **API Flexible:** Can be wrapped in REST APIs, GraphQL, or used directly
