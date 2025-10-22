/**
 * Internal dependencies
 */
import { selectors } from '@moderntribe/events/data/blocks/venue';

const state = {
	events: {
		blocks: {
			venue: {
				blocks: {
					allIds: [ 42 ],
					byId: {},
					core: {},
				},
			},
		},
	},
};

jest.mock( '@moderntribe/common/utils/globals', () => ( {
	dateSettings: () => ( {} ),
	editorDefaults: () => ( {
		venue: 0,
		venueCountry: '',
		venueState: '',
		venueProvince: '',
	} ),
	list: () => ( {
		countries: {},
		us_states: {},
	} ),
	mapsAPI: () => ( {
		embed: true,
	} ),
	wpHooks: {
		addAction: jest.fn(),
		addFilter: jest.fn(),
		removeAction: jest.fn(),
		removeFilter: jest.fn(),
		doAction: jest.fn(),
		applyFilters: jest.fn( ( tag, value ) => value ),
		hasAction: jest.fn( () => false ),
		hasFilter: jest.fn( () => false ),
	},
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
} );
