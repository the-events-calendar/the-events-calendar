/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/venue';

describe( '[STORE] - Venue actions', () => {
	test( 'action to set the venue', () => {
		expect( actions.setVenue( 99 ) ).toMatchSnapshot();
	} );

	test( 'action to set the venue removal', () => {
		expect( actions.removeVenue() ).toMatchSnapshot();
	} );

	test( 'action to set the showMap', () => {
		expect( actions.setShowMap( false ) ).toMatchSnapshot();
		expect( actions.setShowMap( true ) ).toMatchSnapshot();
	} );

	test( 'action to set the showMapLink', () => {
		expect( actions.setShowMapLink( true ) ).toMatchSnapshot();
		expect( actions.setShowMapLink( false ) ).toMatchSnapshot();
	} );
} );
