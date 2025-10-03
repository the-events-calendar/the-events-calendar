/**
 * Set webpack public path dynamically.
 *
 * This file should be imported FIRST in any entry point that uses dynamic imports
 * or loads assets (images, fonts, etc.) at runtime.
 *
 * Usage in your entry point:
 *   import '../../../js/webpack-public-path';
 *
 * @since TBD
 */

// Check if the public path was set by PHP (via wp_head).
if ( typeof window.__webpack_public_path__ !== 'undefined' ) {
	// eslint-disable-next-line camelcase, no-undef
	__webpack_public_path__ = window.__webpack_public_path__;
}
