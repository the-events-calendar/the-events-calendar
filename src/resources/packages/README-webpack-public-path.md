# Webpack Public Path

## webpack-public-path.js

This module sets the webpack public path dynamically at runtime, allowing webpack to correctly resolve asset URLs (images, fonts, etc.) regardless of the WordPress installation location.

### When to Use

Import this module **as the first import** in any package entry point that:
- Uses dynamic `import()` statements
- Imports images, fonts, or other assets
- Uses code splitting

### Usage

```javascript
// MUST be the first import before any other modules
import '../webpack-public-path';

// Now you can safely import assets
import logo from './img/logo.png';
```

### How It Works

1. **PHP Side**: The PHP code (e.g., in Landing Page classes) outputs a `<script>` tag in the `<head>` that sets `window.__webpack_public_path__` to the correct URL
2. **JavaScript Side**: This module reads that value and assigns it to webpack's `__webpack_public_path__` global
3. **Webpack**: When webpack needs to load dynamic imports or assets, it uses this path

### Important Notes

- This must be imported **before** any modules that import assets
- The corresponding PHP code must set `window.__webpack_public_path__` via `admin_head` or similar hook
- The webpack config must have `publicPath: ''` (empty string) in the output configuration
