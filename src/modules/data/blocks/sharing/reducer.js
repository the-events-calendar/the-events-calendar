/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import * as types from './types';

export const DEFAULT_STATE = {
	googleCalendarLabel: __( 'Google Calendar', 'the-events-calendar' ),
	iCalLabel: __( 'iCal Export', 'the-events-calendar' ),
	hasiCal: true,
	hasGoogleCalendar: true,
};

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.SET_GOOGLE_CALENDAR_LABEL:
			return {
				...state,
				googleCalendarLabel: action.payload.label,
			};
		case types.SET_ICAL_LABEL:
			return {
				...state,
				iCalLabel: action.payload.label,
			};
		case types.SET_HAS_GOOGLE_CALENDAR:
			return {
				...state,
				hasGoogleCalendar: action.payload.hasGoogleCalendar,
			};
		case types.TOGGLE_GOOGLE_CALENDAR:
			return {
				...state,
				hasGoogleCalendar: ! state.hasGoogleCalendar,
			};
		case types.SET_HAS_ICAL:
			return {
				...state,
				hasiCal: action.payload.hasiCal,
			};
		case types.TOGGLE_ICAL:
			return {
				...state,
				hasiCal: ! state.hasiCal,
			};
		default:
			return state;
	}
};
