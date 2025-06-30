# Release Process Reusable Workflows

This directory contains reusable workflow components for the release process. These workflows focus purely on task execution and do not handle PR creation. PR creation logic remains in the individual workflow files.

## Workflows

### sync-translations.yml
**Purpose**: Syncs translations and generates POT files

**Inputs**:
- `target-branch`: Target branch for the workflow (default: "main")
- `additional-inputs`: Additional inputs to pass through (JSON string, default: "{}")

**Secrets**:
- `TRANSLATIONS_DEPLOY_HOST`: Required
- `TRANSLATIONS_DEPLOY_USER`: Required
- `TRANSLATIONS_DEPLOY_SSH_KEY`: Required
- `TRANSLATIONS_DEPLOY_POT_LOCATION`: Required

**Outputs**:
- `translation-summary`: Summary of translation changes
- `changes-made`: Whether any changes were made

### process-changelog.yml
**Purpose**: Processes changelog entries for a release

**Inputs**:
- `release-version`: The release version (default: 'figure-it-out')
- `release-date`: The release date (default: "today")
- `action-type`: Action type - amend, generate, or amend-version (default: "generate")
- `target-branch`: Target branch for the workflow (default: "main")
- `additional-inputs`: Additional inputs to pass through (JSON string, default: "{}")

**Outputs**:
- `changelog-content`: The new changelog entry that was generated
- `changes-made`: Whether any changes were made

### analyze-changes.yml
**Purpose**: Analyzes git changes to detect filters, actions, and views modifications

**Inputs**:
- `compare-commit`: Commit to compare against (default: latest tag)
- `output-format`: Output format - changelog, list, or html (default: "changelog")
- `additional-inputs`: Additional inputs to pass through (JSON string, default: "{}")

**Outputs**:
- `analysis-summary`: Summary of analyzed changes
- `changes-detected`: Whether any changes were detected

**Features**:
- Detects added/removed `apply_filters()` calls
- Detects added/removed `do_action()` calls
- Detects changed view files
- Supports multiple output formats (changelog, list, HTML)

### replace-tbd-entries.yml
**Purpose**: Replaces TBD entries with the current version

**Inputs**:
- `target-branch`: Target branch for the workflow (default: "main")
- `additional-inputs`: Additional inputs to pass through (JSON string, default: "{}")

**Outputs**:
- `changes-made`: Whether any changes were made
- `current-version`: The current version found

## Usage

### Individual Usage
Each workflow can be run individually by calling the corresponding workflow file in `.github/workflows/`:
- `release-sync-translations.yml`
- `release-process-changelog.yml`
- `release-analyze-changes.yml`
- `release-replace-tbd-entries.yml`

When run individually, these workflows will:
1. Call the reusable workflow to perform the task
2. Create their own PR if changes are made (PR creation logic remains in the individual workflow files)

### Orchestrated Usage
Use the main orchestration workflow `main-release.yml` to run all steps in sequence and create a single PR with all changes.

## Design Principles

1. **Separation of Concerns**:
   - Reusable workflows focus purely on task execution
   - PR creation logic remains in individual workflow files
   - Main orchestration workflow handles unified PR creation

2. **Reusability**: Workflows can be called individually or as part of orchestration

3. **Extensibility**:
   - All workflows include `additional-inputs` parameter for future extensibility
   - New workflows can be inserted at any point in the main-release.yml process
   - Consistent input/output patterns across all workflows

4. **Consistency**: All workflows follow the same input/output pattern

5. **Flexibility**:
   - Individual workflows create their own PRs when run manually
   - Orchestrated workflow creates a single PR with all changes
   - No PR creation in reusable components

## Architecture

```
Individual Workflow Files (.github/workflows/)
├── release-sync-translations.yml
│   ├── Calls reusable/sync-translations.yml
│   └── Creates PR if run individually
├── release-process-changelog.yml
│   ├── Calls reusable/process-changelog.yml
│   └── Creates PR if run individually
├── release-analyze-changes.yml
│   ├── Calls reusable/analyze-changes.yml
│   └── Creates PR if run individually
└── release-replace-tbd-entries.yml
    ├── Calls reusable/replace-tbd-entries.yml
    └── Creates PR if run individually

Main Orchestration
└── main-release.yml
    ├── Calls all reusable workflows in sequence
    └── Creates single PR with all accumulated changes
```

## Extensibility

All workflows include an `additional-inputs` parameter that accepts a JSON string. This allows for future extensibility without requiring major refactoring. New workflows can be inserted at any point in the main-release.yml process by:

1. Adding the new reusable workflow to this directory
2. Following the same pattern of inputs/outputs
3. Adding the new job to main-release.yml in the appropriate position
4. Passing through any additional inputs using the `additional-inputs` parameter
