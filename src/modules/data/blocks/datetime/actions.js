/**
 * Internal dependencies
 */
import * as types from './types';

export const setNaturalLanguageLabel = ( label ) => ( {
	type: types.SET_NATURAL_LANGUAGE_LABEL,
	payload: {
		label,
	},
} );

export const setDateRange = ( payload ) => ( {
	type: types.SET_DATE_RANGE,
	payload,
} );

export const setStartDateTime = ( start ) => ( {
	type: types.SET_START_DATE_TIME,
	payload: {
		start,
	},
} );

export const setEndDateTime = ( end ) => ( {
	type: types.SET_END_DATE_TIME,
	payload: {
		end,
	},
} );

export const setStartTime = ( start ) => ( {
	type: types.SET_START_TIME,
	payload: {
		start,
	},
} );

export const setEndTime = ( end ) => ( {
	type: types.SET_END_TIME,
	payload: {
		end,
	},
} );

export const setStartTimeInput = ( startTimeInput ) => ( {
	type: types.SET_START_TIME_INPUT,
	payload: {
		startTimeInput,
	},
} );

export const setEndTimeInput = ( endTimeInput ) => ( {
	type: types.SET_END_TIME_INPUT,
	payload: {
		endTimeInput,
	},
} );

export const setSeparatorDate = ( separator ) => ( {
	type: types.SET_SEPARATOR_DATE,
	payload: {
		separator,
	},
} );

export const setSeparatorTime = ( separator ) => ( {
	type: types.SET_SEPARATOR_TIME,
	payload: {
		separator,
	},
} );

export const setAllDay = ( allDay ) => ( {
	type: types.SET_ALL_DAY,
	payload: {
		allDay,
	},
} );

export const setMultiDay = ( multiDay ) => ( {
	type: types.SET_MULTI_DAY,
	payload: {
		multiDay,
	},
} );

export const setTimeZone = ( timeZone ) => ( {
	type: types.SET_TIME_ZONE,
	payload: {
		timeZone,
	},
} );

export const setTimeZoneLabel = ( label ) => ( {
	type: types.SET_TIMEZONE_LABEL,
	payload: {
		label,
	},
} );

export const setTimeZoneVisibility = ( show ) => ( {
	type: types.SET_TIMEZONE_VISIBILITY,
	payload: {
		show,
	},
} );

export const setDateInputVisibility = ( show ) => ( {
	type: types.SET_DATE_INPUT_VISIBILITY,
	payload: {
		show,
	},
} );

export const allowEdits = () => ( {
	type: types.SET_DATETIME_BLOCK_EDITABLE_STATE,
	payload: {
		isEditable: true,
	},
} );

export const disableEdits = () => ( {
	type: types.SET_DATETIME_BLOCK_EDITABLE_STATE,
	payload: {
		isEditable: false,
	},
} );
