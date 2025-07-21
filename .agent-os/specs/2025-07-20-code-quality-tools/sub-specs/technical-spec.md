# Technical Specification

This is the technical specification for the spec detailed in @.agent-os/specs/2025-07-20-code-quality-tools/spec.md

> Created: 2025-07-20
> Version: 1.0.0

## Technical Requirements

- EditorConfig file (.editorconfig) for consistent editor behavior across team members
- PHP-CS-Fixer configuration file (.php-cs-fixer.dist.php) using PER-CS and Symfony standards
- PHPStan configuration file (phpstan.neon) with appropriate strictness level
- Composer scripts for easy command execution (fix, analyse, check)
- GitHub Actions workflow integration for automated quality checks
- Configuration should align with Simensen project patterns and established PHP standards

## Approach Options

**Option A:** Minimal Configuration
- Pros: Simple setup, fewer dependencies, faster execution
- Cons: May miss potential issues, less comprehensive code quality

**Option B:** Comprehensive Configuration (Selected)
- Pros: Thorough code quality checks, catches more potential issues, professional standard
- Cons: Longer setup time, more dependencies

**Rationale:** Since this is a library intended for distribution, comprehensive quality tools are essential for maintaining professional standards and catching issues early. The slight complexity is worth the improved reliability.

## External Dependencies

- **friendsofphp/php-cs-fixer** - Code formatting and style enforcement
- **Justification:** Industry standard for PHP code formatting with extensive configuration options

- **phpstan/phpstan** - Static analysis for type checking and bug detection  
- **Justification:** Leading static analysis tool for PHP with excellent type inference

## Configuration Details

### EditorConfig Settings
- UTF-8 character encoding
- LF line endings
- Final newline insertion
- 4-space indentation for PHP files
- 2-space indentation for JSON, YAML, JS/TS files
- Tab indentation for Makefiles and Neon files

### PHP-CS-Fixer Rules (Based on Simensen Standards)
- @PER-CS and @Symfony rulesets as base
- declare_strict_types enabled
- PHPDoc alignment set to left-aligned
- Custom PHPDoc separation groups for organized documentation
- Global namespace import for constants and functions
- Parallel processing and caching enabled
- Risky fixes allowed for comprehensive formatting

### PHPStan Configuration
- Level 6 analysis (high but not maximum to avoid false positives)
- Scan src/ and tests/ directories
- Bootstrap file for autoloader if needed
- Exclude vendor directories

### Composer Scripts
- `composer fix` - Run PHP-CS-Fixer to format code
- `composer analyse` - Run PHPStan analysis
- `composer check` - Run both tools in read-only mode for CI

## Specific Configuration Files

### .editorconfig Content
```ini
# top-most EditorConfig file
root = true

[*]
charset = utf-8
end_of_line = lf
insert_final_newline = true
indent_style = space
indent_size = 4

[*.json]
indent_size = 2

[*.{yml,yaml}]
indent_size = 2

[*.{js,ts,cjs}]
indent_size = 2

[*.nix]
indent_size = 2

[Makefile]
indent_style = tab

[*.neon]
indent_style = tab
```

### .php-cs-fixer.dist.php Configuration
Based on Simensen project standards with:
- PER-CS and Symfony rulesets
- Strict type declarations
- Left-aligned PHPDoc tags
- Custom PHPDoc separation groups
- Global namespace imports for constants/functions
- Specific rule exemptions for flexibility
- Parallel processing support