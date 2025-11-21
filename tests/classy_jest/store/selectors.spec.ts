// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import { describe, expect, it, jest, beforeEach, afterEach } from '@jest/globals';
import {
	getPostMeta,
	getSettings,
	getEventDateTimeDetails,
	getEditedPostOrganizerIds,
	getEditedPostVenueIds,
	areTicketsSupported,
	isUsingTickets,
	isNewEvent,
	getVenuesLimit,
} from '@tec/events/classy/store/selectors';
import { StoreState } from '@tec/events/classy/types/Store';
import { EventMeta } from '@tec/events/classy/types/EventMeta';
import { Settings } from '@tec/common/classy/types/LocalizedData';
import { TECSettings } from '@tec/events/classy/types/Settings';
import { METADATA_EVENT_ORGANIZER_ID, METADATA_EVENT_VENUE_ID } from '@tec/events/classy/constants';

// Mock the select function from @tec/common/classy/store
jest.mock( '@tec/common/classy/store', () => ( {
	select: jest.fn(),
} ) );

// Mock the areDatesOnSameDay function
jest.mock( '@tec/common/classy/functions', () => ( {
	areDatesOnSameDay: jest.fn(),
} ) );

import { select } from '@tec/common/classy/store';
import { areDatesOnSameDay } from '@tec/common/classy/functions';

const mockSelect = select as jest.MockedFunction< typeof select >;
const mockAreDatesOnSameDay = areDatesOnSameDay as jest.MockedFunction< typeof areDatesOnSameDay >;

describe( 'Store Selectors', () => {
	const mockEventMeta: EventMeta = {
		_EventStartDate: '2024-01-15 10:00:00',
		_EventEndDate: '2024-01-15 18:00:00',
		_EventAllDay: false,
		_EventTimezone: 'America/New_York',
		[ METADATA_EVENT_ORGANIZER_ID ]: [ '1', '2' ],
		[ METADATA_EVENT_VENUE_ID ]: [ '3', '4' ],
	};

	const mockSettings: Settings = {
		timezoneString: 'UTC',
		timezoneChoice: '<select><option value="UTC">UTC</option></select>',
		startOfWeek: 0,
		endOfDayCutoff: { hours: 0, minutes: 0 },
		dateWithYearFormat: 'F j, Y',
		dateWithoutYearFormat: 'F j',
		monthAndYearFormat: 'F Y',
		compactDateFormat: 'n/j/Y',
		dataTimeSeparator: ' @ ',
		timeRangeSeparator: ' - ',
		timeFormat: 'g:i A',
		timeInterval: 15,
		defaultCurrency: {
			code: 'USD',
			symbol: '$',
			position: 'prefix',
		},
	};

	const mockTECSettings: TECSettings = {
		...mockSettings,
		venuesLimit: 5,
	};

	const mockStoreState: StoreState = {
		areTicketsSupported: true,
		isUsingTickets: false,
	};

	beforeEach( () => {
		jest.clearAllMocks();

		// Default mock implementations
		mockSelect.mockImplementation( ( store: string ) => {
			if ( store === 'core/editor' ) {
				return {
					getEditedPostAttribute: jest.fn().mockReturnValue( mockEventMeta ),
				};
			}
			if ( store === 'tec/classy' ) {
				return {
					getSettings: jest.fn().mockReturnValue( mockSettings ),
				};
			}
			return {};
		} );

		// Mock areDatesOnSameDay to return false by default (multiday events)
		mockAreDatesOnSameDay.mockReturnValue( false );
	} );

	afterEach( () => {
		jest.resetAllMocks();
	} );

	describe( 'getPostMeta', () => {
		it( 'returns post meta from editor store', () => {
			const result = getPostMeta();

			expect( mockSelect ).toHaveBeenCalledWith( 'core/editor' );
			expect( result ).toEqual( mockEventMeta );
		} );

		it( 'returns empty object when meta is null', () => {
			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( null ),
					};
				}
				return {};
			} );

			const result = getPostMeta();

			expect( result ).toEqual( {} );
		} );

		it( 'returns empty object when meta is undefined', () => {
			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( undefined ),
					};
				}
				return {};
			} );

			const result = getPostMeta();

			expect( result ).toEqual( {} );
		} );

		it( 'handles missing editor store', () => {
			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return null;
				}
				return {};
			} );

			const result = getPostMeta();

			expect( result ).toEqual( {} );
		} );
	} );

	describe( 'getSettings', () => {
		it( 'returns settings from classy store', () => {
			const result = getSettings();

			expect( mockSelect ).toHaveBeenCalledWith( 'tec/classy' );
			expect( result ).toEqual( mockSettings );
		} );

		it( 'returns empty object when settings are null', () => {
			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'tec/classy' ) {
					return {
						getSettings: jest.fn().mockReturnValue( null ),
					};
				}
				return {};
			} );

			const result = getSettings();

			expect( result ).toEqual( {} );
		} );

		it( 'returns empty object when settings are undefined', () => {
			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'tec/classy' ) {
					return {
						getSettings: jest.fn().mockReturnValue( undefined ),
					};
				}
				return {};
			} );

			const result = getSettings();

			expect( result ).toEqual( {} );
		} );

		it( 'handles missing classy store', () => {
			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'tec/classy' ) {
					return null;
				}
				return {};
			} );

			expect( () => getSettings() ).toThrow();
		} );
	} );

	describe( 'getEventDateTimeDetails', () => {
		it( 'returns event date time details with provided meta', () => {
			const result = getEventDateTimeDetails();

			// Check that dates are valid ISO strings
			expect( result.eventStart ).toMatch( /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/ );
			expect( result.eventEnd ).toMatch( /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/ );
			expect( result.isMultiday ).toBe( true );
			expect( result.isAllDay ).toBe( false );
			expect( result.eventTimezone ).toBe( 'America/New_York' );
			expect( result.timezoneString ).toBe( 'UTC' );
		} );

		it( 'returns default values when meta is empty', () => {
			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( {} ),
					};
				}
				if ( store === 'tec/classy' ) {
					return {
						getSettings: jest.fn().mockReturnValue( mockSettings ),
					};
				}
				return {};
			} );

			const result = getEventDateTimeDetails();

			// Check that dates are valid ISO strings with 8 AM and 5 PM times
			expect( result.eventStart ).toMatch( /^\d{4}-\d{2}-\d{2}T\d{2}:00:00\.000Z$/ );
			expect( result.eventEnd ).toMatch( /^\d{4}-\d{2}-\d{2}T\d{2}:00:00\.000Z$/ );
			expect( result.isMultiday ).toBe( true );
			expect( result.isAllDay ).toBe( false );
			expect( result.eventTimezone ).toBe( 'UTC' );
		} );

		it( 'handles all day events', () => {
			const allDayMeta = {
				...mockEventMeta,
				_EventAllDay: true,
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( allDayMeta ),
					};
				}
				if ( store === 'tec/classy' ) {
					return {
						getSettings: jest.fn().mockReturnValue( mockSettings ),
					};
				}
				return {};
			} );

			const result = getEventDateTimeDetails();

			expect( result.isAllDay ).toBe( true );
		} );

		it( 'uses timezone from meta when available', () => {
			const result = getEventDateTimeDetails();

			expect( result.eventTimezone ).toBe( 'America/New_York' );
		} );

		it( 'falls back to settings timezone when meta timezone is not available', () => {
			const metaWithoutTimezone = {
				...mockEventMeta,
				_EventTimezone: '',
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithoutTimezone ),
					};
				}
				if ( store === 'tec/classy' ) {
					return {
						getSettings: jest.fn().mockReturnValue( mockSettings ),
					};
				}
				return {};
			} );

			const result = getEventDateTimeDetails();

			expect( result.eventTimezone ).toBe( 'UTC' );
		} );

		it( 'calls areDatesOnSameDay with correct parameters', () => {
			getEventDateTimeDetails();

			expect( mockAreDatesOnSameDay ).toHaveBeenCalledWith( expect.any( Date ), expect.any( Date ) );
		} );
	} );

	describe( 'getEditedPostOrganizerIds', () => {
		it( 'returns organizer IDs as numbers', () => {
			const result = getEditedPostOrganizerIds();

			expect( result ).toEqual( [ 1, 2 ] );
			expect( result.every( ( id ) => typeof id === 'number' ) ).toBe( true );
		} );

		it( 'handles string IDs by converting to numbers', () => {
			const metaWithStringIds = {
				...mockEventMeta,
				[ METADATA_EVENT_ORGANIZER_ID ]: [ '10', '20', '30' ],
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithStringIds ),
					};
				}
				return {};
			} );

			const result = getEditedPostOrganizerIds();

			expect( result ).toEqual( [ 10, 20, 30 ] );
		} );

		it( 'handles mixed string and number IDs', () => {
			const metaWithMixedIds = {
				...mockEventMeta,
				[ METADATA_EVENT_ORGANIZER_ID ]: [ '10', 20, '30' ],
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithMixedIds ),
					};
				}
				return {};
			} );

			const result = getEditedPostOrganizerIds();

			expect( result ).toEqual( [ 10, 20, 30 ] );
		} );

		it( 'returns empty array when no organizer IDs', () => {
			const metaWithoutOrganizers = {
				...mockEventMeta,
				[ METADATA_EVENT_ORGANIZER_ID ]: [],
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithoutOrganizers ),
					};
				}
				return {};
			} );

			const result = getEditedPostOrganizerIds();

			expect( result ).toEqual( [] );
		} );

		it( 'returns empty array when organizer IDs are undefined', () => {
			const metaWithoutOrganizers = {
				...mockEventMeta,
			};
			delete metaWithoutOrganizers[ METADATA_EVENT_ORGANIZER_ID ];

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithoutOrganizers ),
					};
				}
				return {};
			} );

			const result = getEditedPostOrganizerIds();

			expect( result ).toEqual( [] );
		} );
	} );

	describe( 'getEditedPostVenueIds', () => {
		it( 'returns venue IDs as numbers', () => {
			const result = getEditedPostVenueIds();

			expect( result ).toEqual( [ 3, 4 ] );
			expect( result.every( ( id ) => typeof id === 'number' ) ).toBe( true );
		} );

		it( 'handles string IDs by converting to numbers', () => {
			const metaWithStringIds = {
				...mockEventMeta,
				[ METADATA_EVENT_VENUE_ID ]: [ '10', '20', '30' ],
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithStringIds ),
					};
				}
				return {};
			} );

			const result = getEditedPostVenueIds();

			expect( result ).toEqual( [ 10, 20, 30 ] );
		} );

		it( 'handles mixed string and number IDs', () => {
			const metaWithMixedIds = {
				...mockEventMeta,
				[ METADATA_EVENT_VENUE_ID ]: [ '10', 20, '30' ],
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithMixedIds ),
					};
				}
				return {};
			} );

			const result = getEditedPostVenueIds();

			expect( result ).toEqual( [ 10, 20, 30 ] );
		} );

		it( 'returns empty array when no venue IDs', () => {
			const metaWithoutVenues = {
				...mockEventMeta,
				[ METADATA_EVENT_VENUE_ID ]: [],
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithoutVenues ),
					};
				}
				return {};
			} );

			const result = getEditedPostVenueIds();

			expect( result ).toEqual( [] );
		} );

		it( 'returns empty array when venue IDs are undefined', () => {
			const metaWithoutVenues = {
				...mockEventMeta,
			};
			delete metaWithoutVenues[ METADATA_EVENT_VENUE_ID ];

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithoutVenues ),
					};
				}
				return {};
			} );

			const result = getEditedPostVenueIds();

			expect( result ).toEqual( [] );
		} );
	} );

	describe( 'areTicketsSupported', () => {
		it( 'returns true when tickets are supported', () => {
			const result = areTicketsSupported( mockStoreState );

			expect( result ).toBe( true );
		} );

		it( 'returns false when tickets are not supported', () => {
			const state: StoreState = {
				areTicketsSupported: false,
				isUsingTickets: false,
			};

			const result = areTicketsSupported( state );

			expect( result ).toBe( false );
		} );

		it( 'returns false when state is undefined', () => {
			const result = areTicketsSupported( undefined as unknown as StoreState );

			expect( result ).toBe( false );
		} );

		it( 'returns false when areTicketsSupported is undefined', () => {
			const state: StoreState = {
				isUsingTickets: false,
			};

			const result = areTicketsSupported( state );

			expect( result ).toBe( false );
		} );
	} );

	describe( 'isUsingTickets', () => {
		it( 'returns true when tickets are supported and being used', () => {
			const state: StoreState = {
				areTicketsSupported: true,
				isUsingTickets: true,
			};

			const result = isUsingTickets( state );

			expect( result ).toBe( true );
		} );

		it( 'returns false when tickets are supported but not being used', () => {
			const result = isUsingTickets( mockStoreState );

			expect( result ).toBe( false );
		} );

		it( 'returns false when tickets are not supported', () => {
			const state: StoreState = {
				areTicketsSupported: false,
				isUsingTickets: true,
			};

			const result = isUsingTickets( state );

			expect( result ).toBe( false );
		} );

		it( 'returns false when both are false', () => {
			const state: StoreState = {
				areTicketsSupported: false,
				isUsingTickets: false,
			};

			const result = isUsingTickets( state );

			expect( result ).toBe( false );
		} );
	} );

	describe( 'isNewEvent', () => {
		it( 'returns true when no start date', () => {
			const metaWithoutStartDate = {
				...mockEventMeta,
				_EventStartDate: '',
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithoutStartDate ),
					};
				}
				return {};
			} );

			const result = isNewEvent();

			expect( result ).toBe( true );
		} );

		it( 'returns true when no end date', () => {
			const metaWithoutEndDate = {
				...mockEventMeta,
				_EventEndDate: '',
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithoutEndDate ),
					};
				}
				return {};
			} );

			const result = isNewEvent();

			expect( result ).toBe( true );
		} );

		it( 'returns false when both dates are present', () => {
			const result = isNewEvent();

			expect( result ).toBe( false );
		} );

		it( 'returns true when both dates are missing', () => {
			const metaWithoutDates = {
				...mockEventMeta,
				_EventStartDate: '',
				_EventEndDate: '',
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'core/editor' ) {
					return {
						getEditedPostAttribute: jest.fn().mockReturnValue( metaWithoutDates ),
					};
				}
				return {};
			} );

			const result = isNewEvent();

			expect( result ).toBe( true );
		} );
	} );

	describe( 'getVenuesLimit', () => {
		it( 'returns venues limit from settings', () => {
			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'tec/classy' ) {
					return {
						getSettings: jest.fn().mockReturnValue( mockTECSettings ),
					};
				}
				return {};
			} );

			const result = getVenuesLimit();

			expect( result ).toBe( 5 );
		} );

		it( 'returns default limit of 1 when not specified', () => {
			const settingsWithoutLimit = {
				...mockSettings,
			};
			delete ( settingsWithoutLimit as any ).venuesLimit;

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'tec/classy' ) {
					return {
						getSettings: jest.fn().mockReturnValue( settingsWithoutLimit ),
					};
				}
				return {};
			} );

			const result = getVenuesLimit();

			expect( result ).toBe( 1 );
		} );

		it( 'returns 0 when limit is negative', () => {
			const settingsWithNegativeLimit = {
				...mockTECSettings,
				venuesLimit: -5,
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'tec/classy' ) {
					return {
						getSettings: jest.fn().mockReturnValue( settingsWithNegativeLimit ),
					};
				}
				return {};
			} );

			const result = getVenuesLimit();

			expect( result ).toBe( 0 );
		} );

		it( 'returns 0 when limit is 0', () => {
			const settingsWithZeroLimit = {
				...mockTECSettings,
				venuesLimit: 0,
			};

			mockSelect.mockImplementation( ( store: string ) => {
				if ( store === 'tec/classy' ) {
					return {
						getSettings: jest.fn().mockReturnValue( settingsWithZeroLimit ),
					};
				}
				return {};
			} );

			const result = getVenuesLimit();

			expect( result ).toBe( 0 );
		} );
	} );
} );
