/**
 * Internal dependencies
 */
import reducer, { DEFAULT_STATE } from '@moderntribe/events/data/blocks/venue/reducer';
import { actions } from '@moderntribe/events/data/blocks/venue';

describe( '[STORE] - Venue reducer', () => {
	it( 'Should set the initial state', () => {
		expect( reducer( undefined, {} ) ).toEqual( DEFAULT_STATE );
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

	it( 'Should toggle the venue map link', () => {
		expect( reducer( DEFAULT_STATE, actions.toggleVenueMapLink() ) ).toMatchSnapshot();
	} );

	it( 'Should toggle the venue map', () => {
		expect( reducer( DEFAULT_STATE, actions.toggleVenueMap() ) ).toMatchSnapshot();
	} );
} );
