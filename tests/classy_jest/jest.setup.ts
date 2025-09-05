/** @jest-environment jsdom */

import { jest } from '@jest/globals';
import { TextDecoder as NodeTextDecoder, TextEncoder as NodeTextEncoder } from 'util';
import { Currency } from '../../common/src/resources/packages/classy/types/Currency';
import { LocalizedData } from '../../common/src/resources/packages/classy/types/LocalizedData';
import { StartOfWeek } from '../../common/src/resources/packages/classy/types/StartOfWeek';
import { Hours } from '../../common/src/resources/packages/classy/types/Hours';
import { Minutes } from '../../common/src/resources/packages/classy/types/Minutes';
import type { StoreDescriptor } from '@wordpress/data';

// Add TextDecoder and TextEncoder to global scope
global.TextDecoder = NodeTextDecoder as unknown as typeof TextDecoder;
global.TextEncoder = NodeTextEncoder as unknown as typeof TextEncoder;

/**
 * @see: https://github.com/WordPress/gutenberg/blob/trunk/test/unit/config/global-mocks.js
 */

// Mock client-zip package
jest.mock( 'client-zip', () => ( {
	downloadZip: jest.fn(),
	makeZip: jest.fn(),
	predictLength: jest.fn(),
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
	media: '',
	onchange: null,
	dispatchEvent: () => true,
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

console.warn = ( msg: string | Error ) => {
	// From the `PostFeaturedImage` component of the `@wordpress/editor` package.
	if ( msg.toString().includes( 'motion() is deprecated. Use motion.create() instead' ) ) {
		return;
	}
	originalWarn( msg );
};

/**
 * Mocks for the global TinyMCE instance loaded on the `window` object by the `wp-tinymce` dependency.
 */
interface TinyMCEMock {
	get: ( id: string ) => {
		initialized: boolean;
		on: jest.Mock;
		off: jest.Mock;
		initialization: boolean;
		init: jest.Mock;
	};
	EditorManager: {
		editors: any[];
	};
}

global.window.tinymce = {
	get: () => ( {
		initialized: true,
		on: jest.fn(),
		off: jest.fn(),
		initialization: false,
		init: jest.fn( ( config: any, callback: () => void ) => {
			callback();
		} ),
	} ),
	EditorManager: {
		editors: [],
	},
} as TinyMCEMock;

global.window.wp = {
	...( global.window.wp || {} ),
	oldEditor: {
		remove: jest.fn(),
		initialize: jest.fn(),
		getContent: jest.fn().mockReturnValue( '<p>Initial content</p>' ),
	},
};

// Setup the localized data for the store.
interface TECWindow extends Window {
	tec: {
		common: {
			classy: {
				data: LocalizedData;
				registry: {
					registerGenericStore: jest.Mock;
					registerStore: jest.Mock;
					subscribe: jest.Mock;
					select: jest.Mock;
					dispatch: jest.Mock;
				};
			};
		};
	};
}

const defaultCurrency: Currency = {
	code: 'USD',
	symbol: '$',
	position: 'prefix',
};

const defaultSettings = {
	timezoneString: 'UTC',
	timezoneChoice:
		'<optgroup label="Africa"><option value="Africa/Abidjan">Abidjan</option></optgroup>' +
		'<optgroup label="Europe"><option value"Europe/Paris">Paris</optionvalue></optgroup>' +
		'<optgroup label="North America"><option value="America/New_York">New York</option></optgroup>' +
		'<optgroup label="UTC"><option value="UTC+0">UTC</option></optgroup>',
	startOfWeek: 0 as StartOfWeek,
	endOfDayCutoff: {
		hours: 0 as Hours,
		minutes: 0 as Minutes,
	},
	dateWithYearFormat: 'F j, Y',
	dateWithoutYearFormat: 'F j',
	monthAndYearFormat: 'F Y',
	compactDateFormat: 'n/j/Y',
	dataTimeSeparator: ' @ ',
	timeRangeSeparator: ' - ',
	timeFormat: 'g:i A',
	timeInterval: 15,
	defaultCurrency,
};

( global.window as unknown as TECWindow ).tec = {
	common: {
		classy: {
			data: {
				settings: defaultSettings,
			},
			registry: {
				registerGenericStore: jest.fn(),
				registerStore: jest.fn(),
				subscribe: jest.fn(),
				select: jest.fn(),
				dispatch: jest.fn(),
			},
		},
	},
};
