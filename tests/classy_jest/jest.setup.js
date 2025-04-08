/**
 * @see: https://github.com/WordPress/gutenberg/blob/trunk/test/unit/config/global-mocks.js
 *
 * client-zip is meant to be used in a browser and is therefore released as an ES6 module only,
 * in order to use it in node environment, we need to mock it.
 * @see: https://github.com/Touffy/client-zip/issues/28
 */

jest.mock( 'client-zip', () => ( {
	downloadZip: jest.fn(),
} ) );

/**
 * @see: https://jestjs.io/docs/manual-mocks#mocking-methods-which-are-not-implemented-in-jsdom
 * @see: https://github.com/WordPress/gutenberg/blob/trunk/packages/jest-preset-default/scripts/setup-globals.js
 */
global.window.matchMedia = () => ( {
	matches: false,
	addListener: () => {},
	addEventListener: () => {},
	removeListener: () => {},
	removeEventListener: () => {},
} );

/**
 * Here we selectively silence some warnings coming from external (e.g. WordPress) components.
 * This should not be abused: legitimate warnings coming from code that is part of the Classy package should
 * be addressed and fixed, not silenced.
 */
const originalWarn = console.warn;

console.warn = ( msg ) => {
	// From the `PostFeaturedImage` component of the `@wordpress/editor` package.
	if (
		msg
			.toString()
			.includes( 'motion() is deprecated. Use motion.create() instead' )
	) {
		return;
	}
	originalWarn( msg );
};

/**
 * Mocks for the global TinyMCE instance loaded on the `window` object by the `wp-tinymce` dependency.
 */
global.window.tinymce = {
	get: () => ( {
		on: () => null,
		off: () => null,
		initialized: true,
	} ),
};

global.window.wp = {
	...( global.window.wp || {} ),
	oldEditor: {
		remove: () => null,
		initialize: () => null,
		getContent: () => '<p>Initial content</p>',
	},
};
