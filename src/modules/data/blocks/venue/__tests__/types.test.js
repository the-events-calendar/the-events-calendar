/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/venue';
import { PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';

describe( '[STORE] - Venue types', () => {
	it( 'Should return the venue types', () => {
		expect( types.SET_VENUE ).toBe( `${ PREFIX_EVENTS_STORE }/SET_VENUE` );
		expect( types.SET_VENUE_MAP_LINK ).toBe( `${ PREFIX_EVENTS_STORE }/SET_VENUE_MAP_LINK` );
		expect( types.SET_VENUE_MAP ).toBe( `${ PREFIX_EVENTS_STORE }/SET_VENUE_MAP` );
		expect( types.TOGGLE_VENUE_MAP ).toBe( `${ PREFIX_EVENTS_STORE }/TOGGLE_VENUE_MAP` );
		expect( types.TOGGLE_VENUE_MAP_LINK ).toBe( `${ PREFIX_EVENTS_STORE }/TOGGLE_VENUE_MAP_LINK` );
	} );
} );
