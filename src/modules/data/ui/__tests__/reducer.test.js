/**
 * External dependencies
 */
import moment from 'moment';

/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/ui';
import reducer, { DEFAULT_STATE } from '@moderntribe/events/data/ui/reducer';

describe( '[STORE] - UI reducer', () => {
	it( 'Should return the default state', () => {
		expect( reducer( undefined, {} ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should open the Dashboard price', () => {
		expect( reducer( {}, actions.openDashboardPrice() ) ).toMatchSnapshot();
	} );

	it( 'Should Set the Dashboard date time by open it', () => {
		const current = reducer( {}, actions.openDashboardDateTime() );
		expect( current ).toMatchSnapshot();
	} );

	it( 'Should set the Dashboard date time by closing it', () => {
		const current = reducer( {}, actions.closeDashboardDateTime() );
		expect( current ).toMatchSnapshot();
	} );

	it( 'Should close the dashaboard price', () => {
		expect( reducer( {}, actions.closeDashboardPrice() ) ).toMatchSnapshot();
	} );

	it( 'Should Toggle Dashboard date time', () => {
		const current = reducer( { dashboardDateTimeOpen: false }, actions.toggleDashboardDateTime() );
		expect( current ).toMatchSnapshot();
	} );

	it( 'Should Set visibility month', () => {
		const visibleMonth = moment().startOf( 'month' ).toDate();
		const current = reducer( {}, actions.setVisibleMonth( visibleMonth ) );
		const expected = {
			visibleMonth,
		};
		expect( current ).toEqual( expected );
	} );
} );
