/**
 * External dependencies
 */
import configureStore from 'redux-mock-store';
import thunk from 'redux-thunk';


/**
 * Internal dependencies
 */
import { thunks } from '@moderntribe/events/data/blocks/datetime';

const middlewares = [ thunk ];
const mockStore = configureStore( middlewares );

let initialState = {};
let store;

jest.mock( '@wordpress/data', () => ( {
	select: () => ( {
		isEditedPostNew: () => {},
	} ),
} ) );

describe( '[STORE] - Datetime thunks', () => {
	beforeEach( () => {
		initialState = {
			blocks: {
				datetime: {
					start: '2018-06-05 17:00:00',
					end: '2018-06-05 17:30:00',
					startTimeInput: '17:00',
					endTimeInput: '17:30',
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
} );
