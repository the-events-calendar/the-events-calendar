/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/ui';
import { PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';

describe( '[STORE] - UI types', () => {
	it( 'Should match the types values', () => {
		expect( types.SET_DASHBOARD_DATE_TIME )
			.toBe( `${ PREFIX_EVENTS_STORE }/SET_DASHBOARD_DATE_TIME` );
		expect( types.TOGGLE_DASHBOARD_DATE_TIME )
			.toBe( `${ PREFIX_EVENTS_STORE }/TOGGLE_DASHBOARD_DATE_TIME` );
		expect( types.SET_VISIBLE_MONTH ).toBe( `${ PREFIX_EVENTS_STORE }/SET_VISIBLE_MONTH` );
	} );
} );
