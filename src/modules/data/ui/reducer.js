/**
 * External dependencies
 */
import moment from 'moment/moment';

/**
 * Internal dependencies
 */
import * as types from './types';

export const DEFAULT_STATE = {
	dashboardDateTimeOpen: false,
	dashboardPriceOpen: false,
	visibleMonth: moment().startOf( 'month' ).toDate(),
};

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.SET_DASHBOARD_DATE_TIME:
			return {
				...state,
				dashboardDateTimeOpen: action.payload.open,
			};
		case types.TOGGLE_DASHBOARD_DATE_TIME:
			return {
				...state,
				dashboardDateTimeOpen: ! state.dashboardDateTimeOpen,
			};
		case types.SET_DASHBOARD_PRICE:
			return {
				...state,
				dashboardPriceOpen: action.payload.open,
			};
		case types.SET_VISIBLE_MONTH:
			return {
				...state,
				visibleMonth: action.payload.visibleMonth,
			};
		default:
			return state;
	}
};
