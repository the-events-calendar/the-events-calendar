/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/sharing';
import reducer, { DEFAULT_STATE } from '@moderntribe/events/data/blocks/sharing/reducer';

describe( '[STORE] - Sharing reducer', () => {
	it( 'Should return the default state', () => {
		expect( reducer( undefined, {} ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should set the iCal Label', () => {
		expect( reducer( DEFAULT_STATE, actions.setiCalLabel( 'Modern Tribe iCal' ) ) )
			.toMatchSnapshot();
	} );

	it( 'Should set the google calendar label', () => {
		const expected = reducer(
			DEFAULT_STATE,
			actions.setGoogleCalendarLabel( 'Modern Tribe Google Calendar' )
		);
		expect( expected ).toMatchSnapshot();
	} );

	it( 'Should set if has the google calendar label', () => {
		expect( reducer( DEFAULT_STATE, actions.setHasGoogleCalendar( true ) ) ).toMatchSnapshot();
		expect( reducer( DEFAULT_STATE, actions.setHasGoogleCalendar( false ) ) ).toMatchSnapshot();
	} );

	it( 'Should set if has the iCal Label', () => {
		expect( reducer( DEFAULT_STATE, actions.setHasIcal( true ) ) ).toMatchSnapshot();
		expect( reducer( DEFAULT_STATE, actions.setHasIcal( false ) ) ).toMatchSnapshot();
	} );

	it( 'Should toggle if has the iCal Label', () => {
		expect( reducer( DEFAULT_STATE, actions.toggleIcalLabel() ) ).toMatchSnapshot();
	} );

	it( 'Should toggle if has the google label', () => {
		expect( reducer( DEFAULT_STATE, actions.toggleGoogleCalendar() ) ).toMatchSnapshot();
	} );
} );
