# GitHub Actions Workflows Documentation

This document describes the reusable workflows and their usage patterns in The Events Calendar plugin.

## Overview

The workflows have been refactored to eliminate redundancy and improve maintainability by using reusable workflows. This reduces code duplication and ensures consistent behavior across different test suites.

## Reusable Workflows

### 1. Slic Test Runner (`reusable/slic-test-runner.yml`)

**Purpose**: Complete slic-based test execution with WordPress container setup, composer dependencies, and test running.

**When to use**: For any test suite that requires the full slic environment (WordPress, composer, Docker containers).

**Inputs**:
- `suite` (required): The test suite name to run (e.g., `views_core`, `views_integration`)
- `skip-flag` (optional): Skip flag to check for (e.g., `views-core`, `views-integration`)
- `wordpress-version` (optional): WordPress version to use (default: `6.6`)
- `additional-setup` (optional): Additional setup commands to run before tests

**Secrets**:
- `gh-bot-token` (optional): GitHub bot token for checkout

**Example Usage**:
```yaml
jobs:
  test:
    uses: ./.github/workflows/reusable/slic-test-runner.yml
    with:
      suite: views_core
      skip-flag: views-core
      wordpress-version: 6.6
      additional-setup: |
        ${SLIC_BIN} wp plugin install wordpress-seo
    secrets:
      gh-bot-token: ${{ secrets.GH_BOT_TOKEN }}
```

### 2. PHP Change Detector (`reusable/php-change-detector.yml`)

**Purpose**: Detects PHP file changes in pull requests and determines if workflows should run.

**When to use**: For workflows that should only run when PHP files have changed.

**Inputs**:
- `skip-flag` (optional): Skip flag to check for (e.g., `phpcs`)

**Outputs**:
- `has-php-changes`: Whether PHP files have changed (`0` or `1`)
- `should-run`: Whether the workflow should run (`0` or `1`)

**Example Usage**:
```yaml
jobs:
  conditional:
    uses: ./.github/workflows/reusable/php-change-detector.yml
    with:
      skip-flag: phpcs
    outputs:
      has-php-changes: ${{ jobs.conditional.outputs.has-php-changes }}
      should-run: ${{ jobs.conditional.outputs.should-run }}

  main-job:
    needs: [conditional]
    if: needs.conditional.outputs.should-run == '1'
    # ... rest of job
```

### 3. Basic Setup (`reusable/basic-setup.yml`)

**Purpose**: Common setup steps including checkout, PHP setup, and Node.js setup.

**When to use**: For workflows that need basic environment setup without the full slic environment.

**Inputs**:
- `fetch-depth` (optional): Git fetch depth (default: `1000`)
- `php-version` (optional): PHP version to use (default: `7.4`)
- `node-version-file` (optional): Node version file to use (default: `.nvmrc`)
- `setup-node` (optional): Whether to setup Node.js (default: `false`)
- `setup-php` (optional): Whether to setup PHP (default: `true`)

**Secrets**:
- `gh-bot-token` (optional): GitHub bot token for checkout

**Example Usage**:
```yaml
jobs:
  setup:
    uses: ./.github/workflows/reusable/basic-setup.yml
    with:
      setup-php: false
      setup-node: true
      fetch-depth: 1
    secrets:
      gh-bot-token: ${{ secrets.GH_BOT_TOKEN }}
```

## Skip Flags Parser (`skip-flags-parser.yml`)

**Purpose**: Automatically parses pull request descriptions for skip flags and provides visibility into which workflows are being skipped.

**When it runs**:
- On pull request open
- On pull request synchronization (new commits)
- On pull request description edits

**What it does**:
1. Scans the PR description for all supported skip flags
2. Creates a human-readable summary of skipped workflows
3. Posts a comment on the PR listing the skipped workflows
4. Only comments when skip flags are detected (no spam on normal PRs)

**Supported Skip Flags**:
- `[skip-views-core]` - Skip Core Views Tests
- `[skip-views-integration]` - Skip Views Integration Tests
- `[skip-views-full-suite]` - Skip Full Views Test Suite
- `[skip-tests-php]` - Skip PHP Tests
- `[skip-tests-integrations]` - Skip Third-Party Integration Tests
- `[skip-phpcs]` - Skip PHP CodeSniffer
- `[skip-phpstan]` - Skip PHPStan Static Analysis
- `[skip-lint]` - Skip Linting

**Example Output**:
When a PR contains `[skip-views-core]` and `[skip-lint]`, the parser will comment:
```
## Skipped Workflows:
- Core Views Tests
- Linting
```

**Benefits**:
- **Transparency**: Clear visibility into which workflows are being skipped
- **Documentation**: Automatic documentation of skip decisions
- **Team Communication**: Helps team members understand what's being tested
- **Audit Trail**: Historical record of workflow skips in PR comments

## Skip Flags

All workflows support skip flags that can be added to pull request descriptions:

- `[skip-views-core]` - Skip Core Views Tests
- `[skip-views-integration]` - Skip Views Integration Tests
- `[skip-views-full-suite]` - Skip Full Views Test Suite
- `[skip-tests-php]` - Skip PHP Tests
- `[skip-tests-integrations]` - Skip Third-Party Integration Tests
- `[skip-phpcs]` - Skip PHP CodeSniffer
- `[skip-phpstan]` - Skip PHPStan Static Analysis
- `[skip-lint]` - Skip Linting

## Workflow Categories

### Test Workflows
- **`views-core.yml`**: Core Views Tests (uses `slic-test-runner`)
- **`views-integration.yml`**: Views Integration Tests (uses `slic-test-runner`)
- **`views-full-suite.yml`**: Full Views Test Suite (uses `slic-test-runner`)
- **`tests-php.yml`**: PHP Tests (uses `slic-test-runner`)
- **`tests-integrations.yml`**: Third-Party Integration Tests (uses `slic-test-runner`)
- **`tests-php-seo-plugin.yml`**: SEO Plugin Tests (uses `slic-test-runner`)

### Quality Assurance Workflows
- **`phpcs.yml`**: PHP CodeSniffer (uses `php-change-detector`)
- **`phpstan.yml`**: PHPStan Static Analysis
- **`lint.yml`**: JavaScript/PostCSS Linting (uses `basic-setup`)

### Utility Workflows
- **`skip-flags-parser.yml`**: Parses skip flags and comments on PRs

## Migration Guide

### Before (Redundant Code)
```yaml
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout the repository
        uses: actions/checkout@v4
        with:
          fetch-depth: 1000
          submodules: recursive
      - name: Configure PHP environment
        uses: shivammathur/setup-php@v2
        with:
          php-version: 7.4
      # ... 50+ more lines of slic setup
```

### After (Reusable Workflow)
```yaml
jobs:
  test:
    uses: ./.github/workflows/reusable/slic-test-runner.yml
    with:
      suite: views_core
      skip-flag: views-core
    secrets:
      gh-bot-token: ${{ secrets.GH_BOT_TOKEN }}
```

## Benefits

1. **Reduced Code Duplication**: Eliminated ~300 lines of duplicated code across workflows
2. **Consistent Behavior**: All slic-based tests use identical setup and execution logic
3. **Easier Maintenance**: Changes to slic setup only need to be made in one place
4. **Better Testing**: Skip flags and PHP change detection are standardized
5. **Improved Readability**: Workflows focus on their specific purpose rather than setup details
6. **Enhanced Visibility**: Skip flags parser provides clear documentation of workflow skips

## Troubleshooting

### Common Issues

1. **Workflow not running**: Check if skip flags are present in PR description
2. **PHP change detection not working**: Ensure the workflow uses `php-change-detector` or `slic-test-runner`
3. **Slic setup failures**: Check that the `gh-bot-token` secret is properly configured
4. **Skip flags not being detected**: Verify the skip flag syntax matches exactly (e.g., `[skip-views-core]`)

### Debugging

- Check workflow step summaries for detailed information about file changes
- Review the skip flags parser output for information about skipped workflows
- Examine the slic setup logs for container and dependency issues
- Look for skip flags parser comments on PRs to understand what's being skipped

## Contributing

When adding new workflows:

1. **Use existing reusable workflows** when possible
2. **Create new reusable workflows** for common patterns that don't fit existing ones
3. **Update this documentation** when adding new reusable workflows
4. **Follow the naming conventions** for skip flags and workflow names
5. **Add new skip flags** to the skip-flags-parser workflow when creating new skippable workflows
