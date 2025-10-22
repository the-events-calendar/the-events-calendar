/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/venue';
import reducer from '@moderntribe/events/data/blocks/venue/reducer';

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

describe( '[STORE] - Venue reducer', () => {
	it( 'Should return the default state', () => {
		expect( reducer( undefined, {} ) ).toMatchSnapshot();
	} );

	it( 'Should set the venue', () => {
		expect( reducer( {}, actions.setVenue( 99 ) ) ).toMatchSnapshot();
	} );

	it( 'Should remove the venue', () => {
		expect( reducer( {}, actions.removeVenue() ) ).toMatchSnapshot();
	} );
} );
