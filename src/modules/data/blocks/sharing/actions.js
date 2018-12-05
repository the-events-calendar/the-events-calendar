/**
 * Internal dependencies
 */
import * as types from './types';
import { DEFAULT_STATE } from './reducer';

export const setGoogleCalendarLabel = ( label ) => ( {
	type: types.SET_GOOGLE_CALENDAR_LABEL,
	payload: {
		label,
	},
} );

export const setHasGoogleCalendar = ( hasGoogleCalendar ) => ( {
	type: types.SET_HAS_GOOGLE_CALENDAR,
	payload: {
		hasGoogleCalendar,
	},
} );

export const toggleGoogleCalendar = () => ( { type: types.TOGGLE_GOOGLE_CALENDAR } );

export const setiCalLabel = ( label ) => ( {
	type: types.SET_ICAL_LABEL,
	payload: {
		label,
	},
} );

export const setHasIcal = ( hasiCal ) => ( {
	type: types.SET_HAS_ICAL,
	payload: {
		hasiCal,
	},
} );

export const toggleIcalLabel = () => ( { type: types.TOGGLE_ICAL } );

export const setInitialState = ( props ) => ( {
	type: types.SET_INITIAL_STATE,
	payload: props,
} );
