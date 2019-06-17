/**
 * External dependencies
 */
import configureStore from 'redux-mock-store';
import thunk from 'redux-thunk';
/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/sharing';

const middlewares = [ thunk ];
const mockStore = configureStore( middlewares );

describe( '[STORE] - Sharing actions', () => {
	it( 'Should set initial state', () => {
		expect( actions.setInitialState( {} ) ).toMatchSnapshot();
	} );

	it( 'Should set the iCal Label', () => {
		expect( actions.setiCalLabel( 'Modern Tribe iCal' ) ).toMatchSnapshot();
	} );

	it( 'Should set the Google calendar label', () => {
		expect( actions.setGoogleCalendarLabel( 'Modern Tribe Google Calendar' ) ).toMatchSnapshot();
	} );

	it( 'Should set if has the google calendar', () => {
		expect( actions.setHasGoogleCalendar( true ) ).toMatchSnapshot();
		expect( actions.setHasGoogleCalendar( false ) ).toMatchSnapshot();
	} );

	it( 'Should set if has the iCal Calendar', () => {
		expect( actions.setHasIcal( true ) ).toMatchSnapshot();
		expect( actions.setHasIcal( false ) ).toMatchSnapshot();
	} );

	it( 'Should toggle if has the google label', () => {
		expect( actions.toggleGoogleCalendar() ).toMatchSnapshot();
	} );

	it( 'Should toggle if has the iCal label', () => {
		expect( actions.toggleIcalLabel() ).toMatchSnapshot();
	} );
} );
