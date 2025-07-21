# Spec Tasks

These are the tasks to be completed for the spec detailed in @.agent-os/specs/2025-07-20-code-quality-tools/spec.md

> Created: 2025-07-20
> Status: Ready for Implementation

## Tasks

- [x] 1. Create EditorConfig Configuration
  - [x] 1.1 Create .editorconfig file with Simensen project standards
  - [x] 1.2 Configure UTF-8 encoding, LF line endings, and indentation rules
  - [x] 1.3 Set specific rules for different file types (JSON, YAML, Neon)
  - [x] 1.4 Test EditorConfig with different editors to verify behavior

- [x] 2. Install and Configure PHP-CS-Fixer
  - [x] 2.1 Add PHP-CS-Fixer to composer.json dev dependencies
  - [x] 2.2 Create .php-cs-fixer.dist.php configuration file with PER-CS and Symfony standards
  - [x] 2.3 Configure strict types, PHPDoc alignment, and global namespace imports
  - [x] 2.4 Add composer script for code formatting
  - [x] 2.5 Test PHP-CS-Fixer on existing codebase and verify no breaking changes

- [x] 3. Install and Configure PHPStan
  - [x] 3.1 Add PHPStan to composer.json dev dependencies
  - [x] 3.2 Create phpstan.neon configuration file with level 6 analysis
  - [x] 3.3 Add composer script for static analysis
  - [x] 3.4 Run PHPStan on existing codebase and fix any issues found

- [ ] 4. Create Unified Quality Check Scripts
  - [ ] 4.1 Add composer script that runs both tools in check mode
  - [ ] 4.2 Create helper script for development workflow
  - [ ] 4.3 Document usage in project documentation
  - [ ] 4.4 Verify all scripts work correctly and provide appropriate exit codes

- [ ] 5. Integrate with CI/CD Pipeline
  - [ ] 5.1 Update GitHub Actions workflow to run quality checks
  - [ ] 5.2 Configure workflow to fail on quality issues
  - [ ] 5.3 Test CI integration with intentional quality violations
  - [ ] 5.4 Verify all quality checks pass in CI environment