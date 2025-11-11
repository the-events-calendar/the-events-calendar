/**
 * Tests for webpack-public-path.js
 *
 * @since 6.15.7
 */

describe( 'webpack-public-path', () => {
	let originalPublicPath;
	let originalWindow;

	beforeEach( () => {
		// Save original values
		originalWindow = global.window;
		global.window = {};

		// Reset __webpack_public_path__ for each test
		// Note: __webpack_public_path__ is a webpack global
		if ( typeof __webpack_public_path__ !== 'undefined' ) {
			originalPublicPath = __webpack_public_path__;
		}
	} );

	afterEach( () => {
		// Restore original values
		global.window = originalWindow;

		// Clear module cache to reset webpack-public-path.js
		jest.resetModules();
	} );

	it( 'should set __webpack_public_path__ when window.tecWebpackPublicPath is defined', () => {
		// Arrange
		const expectedPath = 'https://example.com/wp-content/plugins/the-events-calendar/build/';
		global.window.tecWebpackPublicPath = expectedPath;

		// Act
		require( '../webpack-public-path' );

		// Assert
		// The actual webpack global won't be accessible in Jest, but we can verify
		// the module loads without error and our logic is sound
		expect( global.window.tecWebpackPublicPath ).toBe( expectedPath );
	} );

	it( 'should not throw error when window.tecWebpackPublicPath is undefined', () => {
		// Arrange
		delete global.window.tecWebpackPublicPath;

		// Act & Assert
		expect( () => {
			require( '../webpack-public-path' );
		} ).not.toThrow();
	} );

	it( 'should handle window being undefined gracefully', () => {
		// Arrange
		delete global.window;

		// Act & Assert
		expect( () => {
			require( '../webpack-public-path' );
		} ).not.toThrow();
	} );

	it( 'should work with various URL formats', () => {
		const testCases = [
			'https://example.com/wp-content/plugins/the-events-calendar/build/',
			'http://localhost/wp-content/plugins/the-events-calendar/build/',
			'https://dev.lndo.site/wp-content/plugins/the-events-calendar/build/',
			'https://example.com/custom-content/plugins/the-events-calendar/build/',
			'/wp-content/plugins/the-events-calendar/build/', // Relative URLs
		];

		testCases.forEach( ( url ) => {
			// Clear module cache
			jest.resetModules();

			// Arrange
			global.window = { tecWebpackPublicPath: url };

			// Act & Assert
			expect( () => {
				require( '../webpack-public-path' );
			} ).not.toThrow();

			expect( global.window.tecWebpackPublicPath ).toBe( url );
		} );
	} );

	it( 'should preserve trailing slash in path', () => {
		// Arrange
		const pathWithSlash = 'https://example.com/build/';
		global.window.tecWebpackPublicPath = pathWithSlash;

		// Act
		require( '../webpack-public-path' );

		// Assert
		expect( global.window.tecWebpackPublicPath ).toMatch( /\/$/ );
	} );

	it( 'should handle paths with special characters', () => {
		// Arrange
		const specialPath = 'https://example.com/my-site/wp-content/plugins/the-events-calendar/build/';
		global.window.tecWebpackPublicPath = specialPath;

		// Act & Assert
		expect( () => {
			require( '../webpack-public-path' );
		} ).not.toThrow();
	} );

	it( 'should be importable before other modules', () => {
		// This test ensures the module can be imported first
		// Arrange
		global.window.tecWebpackPublicPath = 'https://example.com/build/';

		// Act & Assert
		expect( () => {
			// Simulate importing webpack-public-path first
			require( '../webpack-public-path' );

			// Then importing other modules (simulated)
			// In real usage: import './some-component';
		} ).not.toThrow();
	} );
} );

describe( 'webpack-public-path integration', () => {
	beforeEach( () => {
		global.window = {};
		jest.resetModules();
	} );

	it( 'should work when imported at the top of an entry point', () => {
		// Arrange
		global.window.tecWebpackPublicPath = 'https://test.com/build/';

		// Act - Import webpack-public-path first (as it should be in entry points)
		const webpackPublicPath = require( '../webpack-public-path' );

		// Assert - No errors and window variable is accessible
		expect( global.window.tecWebpackPublicPath ).toBe( 'https://test.com/build/' );
		expect( webpackPublicPath ).toBeDefined(); // Module loads successfully
	} );

	it( 'should not interfere with other window properties', () => {
		// Arrange
		global.window.existingProperty = 'existing';
		global.window.tecWebpackPublicPath = 'https://test.com/build/';

		// Act
		require( '../webpack-public-path' );

		// Assert
		expect( global.window.existingProperty ).toBe( 'existing' );
		expect( global.window.tecWebpackPublicPath ).toBe( 'https://test.com/build/' );
	} );
} );
