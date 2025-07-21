# Technical Specification

This is the technical specification for the spec detailed in @.agent-os/specs/2025-07-20-composer-package-setup/spec.md

> Created: 2025-07-20
> Version: 1.0.0

## Technical Requirements

- Composer.json optimization following Composer v2 best practices and schema validation
- PSR-4 autoloading configuration with optimized class mapping for production performance
- Semantic versioning compliance with clear version constraint specifications for dependencies
- Package metadata optimization for Packagist search and discovery algorithms
- Development tool management via Phive instead of Composer for clean dependency separation
- License and copyright compliance for open source distribution
- Autoloading optimization for both development and production environments
- Package validation ensuring compatibility with Composer's strict validation rules

## Approach Options

**Option A: Composer-based Development Tools**
- Pros: Simple installation, familiar to most PHP developers, version locked with project
- Cons: Pollutes production dependencies, larger vendor directory, potential conflicts

**Option B: Phive-based Development Tools** (Selected)
- Pros: Clean separation of runtime vs development tools, smaller production footprint, no dependency conflicts
- Cons: Additional tool to learn, requires Phive installation

**Option C: Global Tool Installation**
- Pros: Tools available system-wide, no project pollution
- Cons: Version inconsistency across projects, harder to maintain team consistency

**Rationale:** Option B with Phive provides the cleanest separation between runtime dependencies and development tools. This approach keeps composer.json focused on actual library dependencies while managing quality tools (PHPUnit, PHPStan, PHP-CS-Fixer) separately, resulting in a more professional package distribution.

## External Dependencies

**Production Dependencies (keep minimal):**
- **nesbot/carbon** - Already in use for date/time manipulation
- **illuminate/collections** - Already in use for collection operations
- **dragonmantank/cron-expression** - Required for cron scheduling functionality

**Development Tools (managed by Phive):**
- **phpunit/phpunit** - Testing framework (replace composer dependency)
- **phpstan/phpstan** - Static analysis tool
- **friendsofphp/php-cs-fixer** - Code style fixing

## Phive Configuration

Create `.phive/phars.xml` for tool version management:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phive xmlns="https://phar.io/phive">
  <phar name="phpunit" version="^10.0" installed="10.5.48" location="./tools/phpunit" copy="true"/>
  <phar name="phpstan" version="^1.0" installed="1.10.57" location="./tools/phpstan" copy="true"/>
  <phar name="php-cs-fixer" version="^3.0" installed="3.48.0" location="./tools/php-cs-fixer" copy="true"/>
</phive>
```

## Composer.json Structure

### Package Metadata
- Comprehensive description highlighting ephemeral todo concept
- Keywords for discoverability: "todo", "ephemeral", "scheduling", "productivity", "task-management"
- Homepage, support, and funding information
- Clear license specification (MIT recommended for library adoption)

### Autoloading Configuration
- PSR-4 autoloading for `Simensen\\EphemeralTodos\\` namespace
- Development autoloading for tests
- Optimized class mapping for production use

### Version Constraints
- Minimum PHP 8.1 requirement
- Conservative dependency version constraints for maximum compatibility
- Clear development vs production dependency separation

## Migration Strategy

1. **Remove development tools from composer.json require-dev**
2. **Install and configure Phive for project**
3. **Update CI/CD scripts to use tools/ directory**
4. **Update documentation to reference Phive installation**
5. **Test package installation in clean environment**