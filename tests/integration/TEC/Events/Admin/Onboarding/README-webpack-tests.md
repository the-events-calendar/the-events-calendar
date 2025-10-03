# Webpack Public Path Tests

## Overview

These tests verify that the dynamic webpack public path configuration works correctly in all scenarios, especially with non-standard WordPress installations (custom `WP_CONTENT_DIR`, symlinked plugins, subdirectory installs).

## Test Files

### PHP Integration Tests
- `Landing_Page_Webpack_Test.php` - Integration tests for TEC onboarding landing page webpack configuration

### JavaScript Unit Tests
- `src/resources/packages/__tests__/webpack-public-path.test.js` - Unit tests for the webpack-public-path.js module

## Running the Tests

### PHP Integration Tests
```bash
# Run all Landing Page integration tests
slic run integration --filter=Landing_Page_Webpack_Test

# Run a specific test
slic run integration --filter=it_should_work_with_custom_wp_content_dir
```

### JavaScript Tests
```bash
# Run all webpack-public-path tests
npm test -- webpack-public-path

# Run in watch mode
npm test -- --watch webpack-public-path
```

## Test Coverage

### PHP Test Coverage

1. **Basic Functionality**
   - `it_should_output_webpack_public_path_script` - Verifies script tag output
   - `it_should_include_build_directory_in_path` - Ensures `/build/` is in path
   - `it_should_output_valid_url` - Validates URL format

2. **Conditional Output**
   - `it_should_not_output_when_not_on_landing_page` - Only outputs on correct page

3. **Custom WordPress Configurations** ⭐
   - `it_should_work_with_custom_wp_content_dir` - Tests with custom `WP_CONTENT_DIR`
   - `it_should_handle_symlinked_plugin_directories` - Tests symlinked plugins

4. **Security**
   - `it_should_escape_url_for_javascript` - Ensures proper JSON escaping

5. **Integration**
   - `it_should_hook_into_admin_head` - Verifies hook registration and priority

### JavaScript Test Coverage

1. **Core Functionality**
   - Sets `__webpack_public_path__` when `window.tecWebpackPublicPath` is defined
   - Handles undefined variables gracefully
   - Works with various URL formats

2. **Edge Cases**
   - Handles missing window object
   - Preserves trailing slashes
   - Handles special characters in paths

3. **Integration**
   - Can be imported first (before other modules)
   - Doesn't interfere with other window properties
   - Properly namespaced (TEC vs ET)

## Key Test Scenarios

### Scenario 1: Standard WordPress Installation
```
WordPress Root: /var/www/html/
Plugins Dir:    /var/www/html/wp-content/plugins/
Expected URL:   https://example.com/wp-content/plugins/the-events-calendar/build/
Status:         ✅ Tested
```

### Scenario 2: Custom WP_CONTENT_DIR
```
WordPress Root: /var/www/html/
Content Dir:    /custom/path/to/content/
Expected URL:   https://example.com/custom-content/plugins/the-events-calendar/build/
Status:         ✅ Tested
```

### Scenario 3: Subdirectory Installation
```
WordPress Root: /var/www/html/site/
Plugins Dir:    /var/www/html/site/wp-content/plugins/
Expected URL:   https://example.com/site/wp-content/plugins/the-events-calendar/build/
Status:         ✅ Tested
```

### Scenario 4: Symlinked Plugin
```
Real Path:      /home/user/dev/the-events-calendar/
Symlink:        /var/www/html/wp-content/plugins/the-events-calendar -> /home/user/dev/the-events-calendar/
Expected URL:   https://example.com/wp-content/plugins/the-events-calendar/build/
Status:         ✅ Tested
```

### Scenario 5: Local Development (Lando/DDEV/etc)
```
WordPress Root: /app/
Plugins Dir:    /app/wp-content/plugins/
Expected URL:   https://dev.lndo.site/wp-content/plugins/the-events-calendar/build/
Status:         ✅ Tested
```

## What These Tests Verify

### The Solution Works Because:

1. **PHP Side**
   - Uses `plugins_url()` which respects `WP_PLUGIN_URL` and `WP_PLUGIN_DIR` constants
   - Generates absolute URLs dynamically based on WordPress configuration
   - Works with symlinks via `realpath()` handling in WordPress core

2. **JavaScript Side**
   - Reads from namespaced window variable (`window.tecWebpackPublicPath`)
   - Sets webpack's special `__webpack_public_path__` global
   - Bundle-scoped, doesn't affect other webpack bundles

3. **Integration**
   - PHP outputs in `<head>` before scripts load (priority 1)
   - JS imports first in entry point before any asset imports
   - Webpack uses runtime path for all dynamic imports and assets

## Why Custom WP_CONTENT_DIR is Important

Many WordPress installations use custom content directories for:
- Security (moving wp-content outside web root partially)
- Multi-site management
- Version control strategies
- Hosting provider requirements
- Development/staging workflows

The hardcoded path solution breaks in all these scenarios. Our dynamic solution works in all of them.

## Continuous Integration

These tests should be run:
- ✅ On every pull request
- ✅ Before releasing new versions
- ✅ When modifying webpack configuration
- ✅ When modifying Landing_Page classes

## Related Files

- `/src/Events/Admin/Onboarding/Landing_Page.php` - PHP implementation
- `/src/resources/packages/webpack-public-path.js` - JS implementation
- `/src/resources/packages/README-webpack-public-path.md` - Documentation
- `/webpack.config.js` - Webpack configuration (`publicPath: ''`)
