/**
 * Internal dependencies
 */
import reducer, { DEFAULT_STATE, setInitialState } from '@moderntribe/events/data/blocks/venue/reducer';
import { actions } from '@moderntribe/events/data/blocks/venue';

const entityRecord = {
	meta: {
		_EventVenueID: 42,
		_EventShowMap: false,
		_EventShowMapLink: true,
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

describe( '[STORE] - Venue reducer', () => {
	it( 'Should return the initial state', () => {
		expect( reducer( undefined, {} ) ).toMatchSnapshot();
	} );

	it( 'Should set the venue', () => {
		expect( reducer( DEFAULT_STATE, actions.setVenue( 99 ) ) ).toMatchSnapshot();
	} );

	it( 'Should remove the venue', () => {
		expect( reducer( DEFAULT_STATE, actions.removeVenue() ) ).toMatchSnapshot();
	} );

	it( 'Should set the showMap', () => {
		expect( reducer( DEFAULT_STATE, actions.setShowMap( true ) ) ).toMatchSnapshot();
		expect( reducer( DEFAULT_STATE, actions.setShowMap( false ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the showMapLink', () => {
		expect( reducer( DEFAULT_STATE, actions.setShowMapLink( true ) ) ).toMatchSnapshot();
		expect( reducer( DEFAULT_STATE, actions.setShowMapLink( false ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the initial state', () => {
		setInitialState( entityRecord );
		expect( DEFAULT_STATE ).toMatchSnapshot();
	} );
} );
