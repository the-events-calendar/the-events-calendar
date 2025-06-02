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

// Mocking the `scrollIntoView` function; it's not implemented in JSDOM.
// @see: https://github.com/jsdom/jsdom/issues/1695
window.Element.prototype.scrollIntoView = function () {};

/**
 * Here we selectively silence some warnings coming from external (e.g. WordPress) components.
 * This should not be abused: legitimate warnings coming from code that is part of the Classy package should
 * be addressed and fixed, not silenced.
 */
const originalWarn = console.warn;

console.warn = ( msg ) => {
	// From the `PostFeaturedImage` component of the `@wordpress/editor` package.
	if ( msg.toString().includes( 'motion() is deprecated. Use motion.create() instead' ) ) {
		return;
	}
	originalWarn( msg );
};

/**
 * Mocks for the global TinyMCE instance loaded on the `window` object by the `wp-tinymce` dependency.
 */
global.window.tinymce = {
	get: () => ( {
		initialized: true,
		on: jest.fn(),
		off: jest.fn(),
		initialization: false,
		init: jest.fn( ( config, callback ) => {
			callback();
		} ),
	} ),
	EditorManager: {
		editors: [],
	},
};

global.window.wp = {
	...( global.window.wp || {} ),
	oldEditor: {
		remove: jest.fn(),
		initialize: jest.fn(),
		getContent: jest.fn().mockReturnValue( '<p>Initial content</p>' ),
	},
};

// Setup the localized data for the store.
global.window.tec = global.window.tec || {};
global.window.tec.common = global.window.tec.common || {};
global.window.tec.common.classy = global.window.tec.common.classy || {};
global.window.tec.common.classy.data = global.window.tec.common.classy.data || {
	settings: {
		timezoneString: 'UTC',
		timezoneChoice:
			'<optgroup label="Africa"><option value="Africa/Abidjan">Abidjan</option></optgroup>' +
			'<optgroup label="Europe"><option value"Europe/Paris">Paris</optionvalue></optgroup>' +
			'<optgroup label="North America"><option value="America/New_York">New York</option></optgroup>' +
			'<optgroup label="UTC"><option value="UTC+0">UTC</option></optgroup>',
		startOfWeek: 0,
		endOfDayCutoff: {
			hours: 0,
			minutes: 0,
		},
		dateWithYearFormat: 'F j, Y',
		dateWithoutYearFormat: 'F j',
		monthAndYearFormat: 'F Y',
		compactDateFormat: 'n/j/Y',
		dataTimeSeparator: ' @ ',
		timeRangeSeparator: ' - ',
		timeFormat: 'g:i A',
		timeInterval: 15,
	},
};
