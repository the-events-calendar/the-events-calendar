/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/venue';

describe( '[STORE] - Venue types', () => {
	it( 'Should return the venue types', () => {
		expect( types.SET_VENUE ).toMatchSnapshot();
		expect( types.SET_VENUE_MAP_LINK ).toMatchSnapshot();
		expect( types.SET_VENUE_MAP ).toMatchSnapshot();
	} );
} );
