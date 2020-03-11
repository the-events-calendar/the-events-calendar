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

jest.mock( '@moderntribe/common/utils/globals', () => ( {
	editorDefaults: () => ( {
		venue: 0,
	} ),
	list: () => ( {
		countries: {},
		us_states: {},
	} ),
	mapsAPI: () => ( {
		embed: true,
	} ),
} ) );

describe( '[STORE] - Venue selectors', () => {
	it( 'Should return the map embed config', () => {
		expect( selectors.getMapEmbed() ).toMatchSnapshot();
	} );

	it( 'Should return the venue block', () => {
		expect( selectors.venueBlockSelector( state ) ).toMatchSnapshot();
	} );

	it( 'Should select the showMap', () => {
		expect( selectors.getshowMap( state ) ).toMatchSnapshot();
	} );

	it( 'Should select the showMapLink', () => {
		expect( selectors.getshowMapLink( state ) ).toMatchSnapshot();
	} );

	it( 'Should select the venue', () => {
		expect( selectors.getVenue( state ) ).toMatchSnapshot();
	} );
} );
