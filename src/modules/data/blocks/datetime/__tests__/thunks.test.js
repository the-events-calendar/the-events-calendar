/**
 * External dependencies
 */
import configureStore from 'redux-mock-store';
import thunk from 'redux-thunk';


/**
 * Internal dependencies
 */
import { PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';
import { thunks } from '@moderntribe/events/data/blocks/datetime';

const middlewares = [ thunk ];
const mockStore = configureStore( middlewares );

let initialState = {};
let store;

describe( '[STORE] - Datetime thunks', () => {
	beforeEach( () => {
		initialState = {
			blocks: {
				datetime: {
					start: '2018-06-05 17:00:00',
					end: '2018-06-05 17:30:00',
					dateTimeSeparator: ' @ ',
					timeRangeSeparator: ' - ',
					allDay: false,
					multiDay: false,
					timezone: 'UTC',
				},
			},
		};

		store = mockStore( initialState );
	} );

	test( 'Initial set of state', () => {
		store = mockStore( {} );

		const attributes = {
			start: 'June 5, 2018 5:00 pm',
			end: 'June 5, 2018 5:30 pm',
			dateTimeSeparator: ' @ ',
			timeRangeSeparator: ' - ',
			allDay: false,
			multiDay: false,
			timeZone: 'UTC',
		};

		const get = jest.fn( ( name ) => attributes[ name ] );
		store.dispatch( thunks.setInitialState( { get, attributes } ) );

		expect( get ).toHaveBeenCalled();
		expect( get ).toHaveBeenCalledTimes( 2 );
		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Should set the human readable', () => {
		store = mockStore( {} );
		const attributes = {
			start: '',
			end: '',
		};

		const get = jest.fn( ( name ) => attributes[ name ] );
		store.dispatch( thunks.setInitialState( { get, attributes } ) );

		const types = store.getActions().map( ( action ) => action.type );
		const values = store.getActions().map( ( action ) => action.payload )
			.reduce( ( obj, item ) => {
				return { ...obj, ...item };
			}, {} );
		const expectedActions = [
			`${ PREFIX_EVENTS_STORE }/SET_START_DATE_TIME`,
			`${ PREFIX_EVENTS_STORE }/SET_END_DATE_TIME`,
			`${ PREFIX_EVENTS_STORE }/SET_NATURAL_LANGUAGE_LABEL`,
			`${ PREFIX_EVENTS_STORE }/SET_MULTI_DAY`,
		];
		expect( get ).toHaveBeenCalled();
		expect( get ).toHaveBeenCalledTimes( 2 );
		expect( types ).toEqual( expectedActions );
		expect( values.start ).not.toBe( '' );
		expect( values.end ).not.toBe( '' );
		expect( values.label ).not.toBe( '' );
	} );

	test( 'Set start time', () => {
		const attributes = {
			start: '2018-06-05 17:00:00',
			seconds: 7200,
		};

		store.dispatch( thunks.setStartTime( attributes ) );

		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Set end time', () => {
		Date.now = jest.fn( () => '2018-08-06T05:23:19.000Z' );
		const attributes = {
			end: '2018-06-05 17:30:00',
			seconds: 64800,
		};

		store.dispatch( thunks.setEndTime( attributes ) );

		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Set all day on', () => {
		const attributes = {
			start: '2018-06-05 17:00:00',
			end: '2018-06-05 17:30:00',
			isAllDay: true,
		};

		store.dispatch( thunks.setAllDay( attributes ) );

		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Set all day off', () => {
		const attributes = {
			start: '2018-06-05 00:00:00',
			end: '2018-06-05 23:59:59',
			isAllDay: false,
		};

		store.dispatch( thunks.setAllDay( attributes ) );

		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Set dates from date picker with multi day off', () => {
		const attributes = {
			start: '2018-06-05 17:00:00',
			end: '2018-06-05 17:30:00',
			from: new Date( 'Wed Sep 19 2018 12:00:00' ),
			to: undefined,
		};

		store.dispatch( thunks.setDates( attributes ) );

		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Set dates from date picker with multi day on', () => {
		const attributes = {
			start: '2018-06-05 17:00:00',
			end: '2018-06-05 17:30:00',
			from: new Date( 'Wed Sep 19 2018 12:00:00' ),
			to: new Date( 'Fri Sep 21 2018 12:00:00' ),
		};

		store.dispatch( thunks.setDates( attributes ) );

		expect( store.getActions() ).toMatchSnapshot();
	} );

	describe( 'setDateTime', () => {
		test( 'Set only the start date', () => {
			const attributes = {
				start: '2018-06-10 17:00:00',
			};
			store.dispatch( thunks.setDateTime( attributes ) );
			expect( store.getActions() ).toMatchSnapshot();
		} );

		test( 'Set the start and end date on the same day', () => {
			const attributes = {
				start: '2018-06-10 12:00:00',
				end: '2018-06-10 12:00:00',
			};
			store.dispatch( thunks.setDateTime( attributes ) );
			expect( store.getActions() ).toMatchSnapshot();
		} );

		test( 'Set the start and end date on different days', () => {
			const attributes = {
				start: '2018-05-01 12:00:00',
				end: '2018-05-04 20:00:00',
			};
			store.dispatch( thunks.setDateTime( attributes ) );
			expect( store.getActions() ).toMatchSnapshot();
		} );
	} );

	test( 'Set multi day to true', () => {
		const attributes = {
			start: '2018-06-05 17:00:00',
			end: '2018-06-05 17:30:00',
			isMultiDay: true,
		};

		store.dispatch( thunks.setMultiDay( attributes ) );

		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Set multi day to false when end time is later than start time', () => {
		const attributes = {
			start: '2018-06-05 17:00:00',
			end: '2018-06-08 17:30:00',
			isMultiDay: false,
		};

		store.dispatch( thunks.setMultiDay( attributes ) );

		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Set multi day to false when end time is earlier than start time', () => {
		const attributes = {
			start: '2018-06-05 17:00:00',
			end: '2018-06-08 15:30:00',
			isMultiDay: false,
		};

		store.dispatch( thunks.setMultiDay( attributes ) );

		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Set multi day to false when end time is earlier than start time', () => {
		const attributes = {
			start: '2018-06-05 23:30:00',
			end: '2018-06-08 15:30:00',
			isMultiDay: false,
		};

		store.dispatch( thunks.setMultiDay( attributes ) );

		expect( store.getActions() ).toMatchSnapshot();
	} );
} );
