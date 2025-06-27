# The Events Calendar Test Suites

This directory contains all test suites for The Events Calendar plugin, including the newly refactored views test suites.

## Views Test Suites

The views integration test suite has been refactored into 9 separate, independently runnable test suites:

### Core Test Suites
- **`views-core/`** - Core view implementations (Month, List, Day, etc.)
- **`views-components/`** - Reusable UI components
- **`views-partials/`** - Template partials organized by view type
- **`views-data/`** - Data layer tests (Repository, Query, Utils)
- **`views-seo/`** - SEO functionality
- **`views-integration/`** - Integration features (iCalendar, Template, etc.)
- **`views-blocks/`** - Block editor functionality
- **`views-modules/`** - Module functionality
- **`unit-views/`** - Unit tests for view classes

### Running Views Tests

```bash
# Run all views suites
vendor/bin/codecept run views_core
vendor/bin/codecept run views_components
vendor/bin/codecept run views_partials
vendor/bin/codecept run views_data
vendor/bin/codecept run views_seo
vendor/bin/codecept run views_integration
vendor/bin/codecept run views_blocks
vendor/bin/codecept run views_modules
vendor/bin/codecept run unit_views

# Run specific test
vendor/bin/codecept run views_core Month_ViewTest
```

### Benefits

1. **Performance**: Parallel execution of independent suites
2. **Isolation**: Complete data isolation between suites
3. **Maintainability**: Clear separation of concerns
4. **CI/CD**: Granular workflows with path-based triggers

## Other Test Suites

- **`wpunit/`** - WordPress unit tests
- **`integration/`** - Integration tests
- **`features/`** - Feature tests
- **`restv1/`** - REST API v1 tests
- **`views_rest/`** - Views REST API tests
- **`views_ui/`** - Views UI tests
- **`views_settings/`** - Views settings tests
- **`views_widgets/`** - Views widgets tests
- **`views_wpunit/`** - Views WordPress unit tests
- **`views_v2_customizer_integration/`** - Views v2 customizer tests
- **`blocks_editor_integration/`** - Block editor integration tests
- **`ct1_integration/`** - Custom Tables v1 integration tests
- **`ct1_migration/`** - Custom Tables v1 migration tests
- **`ct1_multisite_integration/`** - Custom Tables v1 multisite tests
- **`ct1_wp_json_api/`** - Custom Tables v1 WP JSON API tests
- **`deprecated/`** - Deprecated functionality tests
- **`elementor_integration/`** - Elementor integration tests
- **`embed_calendar_integration/`** - Embed calendar integration tests
- **`event_status/`** - Event status tests
- **`integrations_plugin_wordpress_seo/`** - WordPress SEO integration tests
- **`muintegration/`** - Must-use plugin integration tests
- **`rewrite_functional/`** - Rewrite functional tests
- **`views_settings/`** - Views settings tests
- **`wp_json_api/`** - WP JSON API tests
- **`wpml_integration/`** - WPML integration tests

## Configuration

Each test suite has its own Codeception configuration file (`.suite.dist.yml`) and bootstrap file (`_bootstrap.php`).

## GitHub Workflows

- **`views-core.yml`** - Always runs on PRs (critical functionality)
- **`views-integration.yml`** - Always runs on PRs (critical functionality)
- **`views-full-suite.yml`** - Scheduled daily runs

For detailed information about the views test suite refactoring, see `REFACTORING_SUMMARY.md`.
