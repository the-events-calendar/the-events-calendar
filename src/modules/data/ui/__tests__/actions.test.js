/**
 * External dependencies
 */
import configureStore from 'redux-mock-store';
import thunk from 'redux-thunk';
import moment from 'moment';

/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/ui';

const middlewares = [ thunk ];
const mockStore = configureStore( middlewares );

describe( '[STORE] - UI actions', () => {
	it( 'Should toggle the dashboard', () => {
		expect( actions.toggleDashboardDateTime() ).toMatchSnapshot();
	} );

	it( 'Should open the dashboard', () => {
		expect( actions.openDashboardDateTime() ).toMatchSnapshot();
		expect( actions.openDashboardPrice() ).toMatchSnapshot();
	} );

	it( 'Should close the dashboard', () => {
		expect( actions.closeDashboardDateTime() ).toMatchSnapshot();
		expect( actions.closeDashboardPrice() ).toMatchSnapshot();
	} );

	it( 'Should set the visible month', () => {
		Date.now = jest.fn( () => '2018-07-01T05:00:00.000Z' );
		expect( actions.setVisibleMonth( Date.now() ) ).toMatchSnapshot();
	} );

	it( 'Should not set the initial state', () => {
		const store = mockStore( {} );
		const get = jest.fn();
		store.dispatch( actions.setInitialState( { get } ) );

		expect( get ).toHaveBeenCalledTimes( 1 );
		expect( store.getActions() ).toEqual( [] );
	} );
} );

describe( '[STORE] - UI thunk actions', () => {
	it( 'Should set the initial state', () => {
		const store = mockStore( {} );
		const get = jest.fn( () => moment( '2018-07-01T00:00:00.000Z' ) );

		store.dispatch( actions.setInitialState( { get } ) );

		expect( get ).toHaveBeenCalled();
		expect( get ).toHaveBeenCalledTimes( 1 );
		expect( store.getActions() ).toMatchSnapshot();
	} );
} );
