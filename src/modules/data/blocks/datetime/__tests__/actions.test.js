/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/datetime';

describe( '[STORE] - Datetime actions', () => {
	test( 'Action to set the start time', () => {
		expect( actions.setStartDateTime( 'June 5, 2018 5:00 pm' ) ).toMatchSnapshot();
	} );

	test( 'Set Natural Language Label', () => {
		expect( actions.setNaturalLanguageLabel( '2 weeks from now' ) ).toMatchSnapshot();
	} );

	test( 'Action to set the start time', () => {
		expect( actions.setEndDateTime( 'June 25, 2018 4:00 pm' ) ).toMatchSnapshot();
	} );

	test( 'Action to set the separator date', () => {
		expect( actions.setSeparatorDate( ' -  ' ) ).toMatchSnapshot();
	} );

	test( 'Action to set the separator time', () => {
		expect( actions.setSeparatorTime( ' @ ' ) ).toMatchSnapshot();
	} );

	test( 'Action to set the time zone', () => {
		expect( actions.setTimeZone( 'UTC' ) ).toMatchSnapshot();
	} );

	test( 'Action to set the time zone label', () => {
		expect( actions.setTimeZoneLabel( 'Modern Tribe' ) ).toMatchSnapshot();
	} );

	test( 'Action to set the visibility of the time zone', () => {
		expect( actions.setTimeZoneVisibility( true ) ).toMatchSnapshot();
		expect( actions.setTimeZoneVisibility( false ) ).toMatchSnapshot();
	} );

	test( 'Action to set all day', () => {
		expect( actions.setAllDay( true ) ).toMatchSnapshot();
		expect( actions.setAllDay( false ) ).toMatchSnapshot();
	} );

	test( 'Action to set the multi day', () => {
		expect( actions.setMultiDay( true ) ).toMatchSnapshot();
		expect( actions.setMultiDay( false ) ).toMatchSnapshot();
	} );

	test( 'Action to set the date input visibility', () => {
		expect( actions.setDateInputVisibility( true ) ).toMatchSnapshot();
		expect( actions.setDateInputVisibility( false ) ).toMatchSnapshot();
	} );
	test( 'Set editable', () => {
		expect( actions.allowEdits() ).toMatchSnapshot();
		expect( actions.disableEdits() ).toMatchSnapshot();
	} );

	test( 'setStartTime', () => {
		expect( actions.setStartTime( 6000 ) ).toMatchSnapshot();
	} );
	test( 'setEndTime', () => {
		expect( actions.setEndTime( 6000 ) ).toMatchSnapshot();
	} );
	test( 'setDateRange', () => {
		expect( actions.setDateRange( { from: 0, to: 1 } ) ).toMatchSnapshot();
	} );
	test( 'setStartTimeInput', () => {
		expect( actions.setStartTimeInput( '18:00' ) ).toMatchSnapshot();
	} );
	test( 'setEndTimeInput', () => {
		expect( actions.setEndTimeInput( '18:00' ) ).toMatchSnapshot();
	} );
} );
