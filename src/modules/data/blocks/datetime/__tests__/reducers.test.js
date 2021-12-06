/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/datetime';
import reducer, {
	DEFAULT_STATE,
	defaultStateToMetaMap,
	setInitialState,
} from '@moderntribe/events/data/blocks/datetime/reducer';

jest.mock( 'moment', () => () => {
	const moment = jest.requireActual( 'moment' );
	return moment( 'July 19, 2018 7:30 pm', 'MMMM D, Y h:mm a' );
} );

const data = {
	meta: {
		_EventStartDate: '2020-04-01 09:30:00',
		_EventEndDate: '2018-04-01 12:15:00',
		_EventDateTimeSeparator: 'at',
		_EventTimeRangeSeparator: 'to',
		_EventAllDay: false,
		_EventTimezone: 'UTC+8',
	},
};

describe( '[STORE] - Datetime reducer', () => {
	it( 'Should set the default state', () => {
		expect( reducer( undefined, {} ) ).toMatchSnapshot();
	} );

	it( 'Should set the start', () => {
		expect( reducer( DEFAULT_STATE, actions.setStartDateTime( 'June 5, 2018 5:00 pm' ) ) )
			.toMatchSnapshot();
	} );

	it( 'Should set the end', () => {
		expect( reducer( DEFAULT_STATE, actions.setEndDateTime( 'June 25, 2018 4:00 pm' ) ) )
			.toMatchSnapshot();
	} );

	it( 'Should set the start time input', () => {
		expect( reducer( DEFAULT_STATE, actions.setStartTimeInput( '18:00' ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the end time input', () => {
		expect( reducer( DEFAULT_STATE, actions.setEndTimeInput( '18:00' ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the separator time', () => {
		expect( reducer( DEFAULT_STATE, actions.setSeparatorTime( ' | ' ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the separator date', () => {
		expect( reducer( DEFAULT_STATE, actions.setSeparatorDate( ' > ' ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the timezone', () => {
		expect( reducer( DEFAULT_STATE, actions.setTimeZone( 'UTC' ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the visibility of the timezone', () => {
		expect( reducer( DEFAULT_STATE, actions.setTimeZoneVisibility( true ) ) ).toMatchSnapshot();
		expect( reducer( DEFAULT_STATE, actions.setTimeZoneVisibility( false ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the all day', () => {
		expect( reducer( DEFAULT_STATE, actions.setAllDay( true ) ) ).toMatchSnapshot();
		expect( reducer( DEFAULT_STATE, actions.setAllDay( false ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the multi day', () => {
		expect( reducer( DEFAULT_STATE, actions.setMultiDay( true ) ) ).toMatchSnapshot();
		expect( reducer( DEFAULT_STATE, actions.setMultiDay( false ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the natural language label', () => {
		expect( reducer( DEFAULT_STATE, actions.setNaturalLanguageLabel( '2 weeks from now' ) ) )
			.toMatchSnapshot();
	} );

	it( 'Should set the editability', () => {
		expect( reducer( DEFAULT_STATE, actions.allowEdits() ) ).toMatchSnapshot();
		expect( reducer( DEFAULT_STATE, actions.disableEdits() ) ).toMatchSnapshot();
	} );

	it( 'Should return the default state to meta map', () => {
		expect( defaultStateToMetaMap ).toMatchSnapshot();
	} );

	it( 'Should set the initial state', () => {
		setInitialState( data );
		expect( DEFAULT_STATE ).toMatchSnapshot();
	} );
} );
