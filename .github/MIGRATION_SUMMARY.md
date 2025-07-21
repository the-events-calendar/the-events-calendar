# Migration Summary: Reusable Workflows → GitHub Actions

This document summarizes the migration from reusable workflows to GitHub Actions for The Events Calendar plugin.

## Files Removed

The following reusable workflow files have been removed as they've been converted to actions:

### Reusable Workflows
- `/.github/workflows/reusable/basic-setup.yml`
- `/.github/workflows/reusable/php-change-detector.yml`
- `/.github/workflows/reusable/slic-test-runner.yml`
- `/.github/workflows/reusable/release-process/analyze-changes.yml`
- `/.github/workflows/reusable/release-process/process-changelog.yml`
- `/.github/workflows/reusable/release-process/replace-tbd-entries.yml`
- `/.github/workflows/reusable/release-process/sync-translations.yml`
- `/.github/workflows/reusable/release-process/README.md`

### Directories Removed
- `/.github/workflows/reusable/release-process/` (empty)
- `/.github/workflows/reusable/` (empty)

## Files Updated

The following workflow files have been updated to use the new actions:

### Main Workflows
- ✅ `changelogger.yml` - Updated to use `basic-setup` action
- ✅ `lint.yml` - Updated to use `basic-setup` action
- ✅ `main-release.yml` - Updated to use all release actions
- ✅ `phpcs.yml` - Updated to use `php-change-detector` action
- ✅ `phpstan.yml` - Updated to use `php-change-detector` and `basic-setup` actions
- ✅ `skip-flags-parser.yml` - Updated to use `basic-setup` action
- ✅ `zip.yml` - Updated to use `basic-setup` action
- ✅ `release-prepare-branch.yml` - Updated to use `basic-setup` action

### Test Workflows
- ✅ `tests-integrations.yml` - Updated to use `slic-test-runner` action
- ✅ `tests-php-seo-plugin.yml` - Updated to use `slic-test-runner` action
- ✅ `tests-php.yml` - Updated to use `slic-test-runner` action

### Release Workflows
- ✅ `release-analyze-changes.yml` - Updated to use `analyze-changes` action
- ✅ `release-process-changelog.yml` - Updated to use `process-changelog` action
- ✅ `release-replace-tbd-entries.yml` - Updated to use `replace-tbd-entries` action
- ✅ `release-sync-translations.yml` - Updated to use `sync-translations` action

## Migration Changes

### 1. Action References
**Before:**
```yaml
uses: ./.github/workflows/reusable/basic-setup.yml
```

**After:**
```yaml
uses: the-events-calendar/actions/.github/actions/basic-setup@main
```

### 2. Input Format Changes
**Boolean inputs now require string values:**

**Before:**
```yaml
with:
  setup-php: true
  setup-node: false
```

**After:**
```yaml
with:
  setup-php: 'true'
  setup-node: 'false'
```

### 3. Secrets → Inputs
**Translation workflows now pass secrets as inputs:**

**Before:**
```yaml
secrets:
  TRANSLATIONS_DEPLOY_HOST: ${{ secrets.TRANSLATIONS_HOST }}
```

**After:**
```yaml
with:
  translations-deploy-host: ${{ secrets.TRANSLATIONS_HOST }}
```

### 4. Job Structure Changes
**Workflows using reusable workflows now include steps:**

**Before:**
```yaml
jobs:
  test:
    uses: ./.github/workflows/reusable/slic-test-runner.yml
    with:
      suite: unit
```

**After:**
```yaml
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: the-events-calendar/actions/.github/actions/slic-test-runner@main
        with:
          suite: unit
```

### 5. Output Access Changes
**Access outputs through step outputs instead of job outputs:**

**Before:**
```yaml
outputs:
  has-php-changes: ${{ jobs.conditional.outputs.has-php-changes }}
```

**After:**
```yaml
outputs:
  has-php-changes: ${{ steps.php-changes.outputs.has-php-changes }}
```

## Actions Available

The following actions are now available from `the-events-calendar/actions`:

### Setup & Infrastructure
- `basic-setup` - Environment setup with PHP and Node.js

### Code Analysis
- `php-change-detector` - Detects PHP file changes for conditional execution

### Testing
- `slic-test-runner` - Runs PHP tests using slic framework

### Release Management
- `analyze-changes` - Analyzes git changes for WordPress hooks and views
- `process-changelog` - Processes changelogs with version detection
- `replace-tbd-entries` - Replaces TBD placeholders with version numbers
- `sync-translations` - Manages translation files and GlotPress integration

## Benefits of Migration

1. **Modularity**: Each action can be used independently
2. **Performance**: Better caching and parallel execution
3. **Maintainability**: Individual versioning and updates
4. **Reusability**: Actions can be called multiple times in workflows
5. **Flexibility**: Easier conditional execution and parameter passing

## Next Steps

1. All workflows have been updated and tested
2. Old reusable workflow files have been removed
3. Actions are ready for use across all plugins
4. Consider pinning to specific action versions for production use

## Verification

To verify the migration was successful:

```bash
# Check for remaining reusable workflow references
grep -r "workflows/reusable" .github/workflows/

# Should return: "No matches found" or empty result
```

## Usage Example

```yaml
name: Plugin CI/CD
on: [pull_request]

jobs:
  setup-and-test:
    runs-on: ubuntu-latest
    steps:
      - uses: the-events-calendar/actions/.github/actions/basic-setup@main
        with:
          setup-php: 'true'
          setup-node: 'true'

      - id: changes
        uses: the-events-calendar/actions/.github/actions/php-change-detector@main

      - if: steps.changes.outputs.should-run == '1'
        uses: the-events-calendar/actions/.github/actions/slic-test-runner@main
        with:
          suite: unit
```
