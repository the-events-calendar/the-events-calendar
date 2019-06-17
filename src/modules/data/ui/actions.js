/**
 * Internal dependencies
 */
import * as types from './types';
import { moment as momentUtil } from '@moderntribe/common/utils';

export const toggleDashboardDateTime = () => ( {
	type: types.TOGGLE_DASHBOARD_DATE_TIME,
} );

export const openDashboardDateTime = () => ( {
	type: types.SET_DASHBOARD_DATE_TIME,
	payload: {
		open: true,
	},
} );

export const closeDashboardDateTime = () => ( {
	type: types.SET_DASHBOARD_DATE_TIME,
	payload: {
		open: false,
	},
} );

export const openDashboardPrice = () => ( {
	type: types.SET_DASHBOARD_PRICE,
	payload: {
		open: true,
	},
} );

export const closeDashboardPrice = () => ( {
	type: types.SET_DASHBOARD_PRICE,
	payload: {
		open: false,
	},
} );

export const setVisibleMonth = ( visibleMonth ) => ( {
	type: types.SET_VISIBLE_MONTH,
	payload: {
		visibleMonth,
	},
} );

export const setInitialState = ( { get } ) => ( dispatch ) => {
	const start = get( 'start' );
	if ( ! start ) {
		return;
	}

	const month = momentUtil.toMoment( momentUtil.parseFormats( start ) ).startOf( 'month' ).toDate();
	dispatch( setVisibleMonth( month ) );
};
