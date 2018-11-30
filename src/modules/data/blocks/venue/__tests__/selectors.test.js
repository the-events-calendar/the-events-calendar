/**
 * Internal dependencies
 */
import { DEFAULT_STATE } from '@moderntribe/events/data/blocks/venue/reducer';
import { selectors } from '@moderntribe/events/data/blocks/venue';

const state = {
	events: {
		blocks: {
			venue: DEFAULT_STATE,
		},
	},
};

describe( '[STORE] - Venue selectors', () => {
	it( 'Should return the venue block', () => {
		expect( selectors.venueBlockSelector( state ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should select the showMap', () => {
		expect( selectors.getshowMap( state ) ).toEqual( DEFAULT_STATE.showMap );
	} );

	it( 'Should select the showMapLink', () => {
		expect( selectors.getshowMapLink( state ) ).toEqual( DEFAULT_STATE.showMapLink );
	} );

	it( 'Should select the venue', () => {
		expect( selectors.getVenue( state ) ).toEqual( DEFAULT_STATE.venue );
	} );
} );
