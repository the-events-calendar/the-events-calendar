/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import moment from 'moment/moment';

/**
 * Internal dependencies
 */
import {
	date,
	moment as momentUtil,
	time,
} from '@moderntribe/common/utils';
import { settings } from '@moderntribe/common/utils/globals';
import * as types from './types';

export const DEFAULT_STATE = {
	start: momentUtil.toDateTime( momentUtil.roundTime( moment() ) ),
	end: momentUtil.toDateTime( momentUtil.roundTime( moment() ).add( time.HALF_HOUR_IN_SECONDS, 'seconds' ) ),
	naturalLanguage: '',
	dateTimeSeparator: settings() && settings().dateTimeSeparator ? settings().dateTimeSeparator : __( '@', 'events-gutenberg' ),
	timeRangeSeparator: settings() && settings().timeRangeSeparator ? settings().timeRangeSeparator : __( '-', 'events-gutenberg' ),
	allDay: false,
	multiDay: false,
	timeZone: date.FORMATS.TIMEZONE.string,
	timeZoneLabel: date.FORMATS.TIMEZONE.string,
	showTimeZone: false,
	showDateInput: false,
	isEditable: true,
};

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.SET_DATETIME_BLOCK_EDITABLE_STATE:
			return {
				...state,
				isEditable: action.payload.isEditable,
			};
		case types.SET_START_DATE_TIME:
			return {
				...state,
				start: action.payload.start,
			};
		case types.SET_END_DATE_TIME:
			return {
				...state,
				end: action.payload.end,
			};
		case types.SET_NATURAL_LANGUAGE_LABEL:
			return {
				...state,
				naturalLanguage: action.payload.label,
			};
		case types.SET_ALL_DAY:
			return {
				...state,
				allDay: action.payload.allDay,
			};
		case types.SET_MULTI_DAY:
			return {
				...state,
				multiDay: action.payload.multiDay,
			};
		case types.SET_SEPARATOR_DATE:
			return {
				...state,
				dateTimeSeparator: action.payload.separator,
			};
		case types.SET_SEPARATOR_TIME:
			return {
				...state,
				timeRangeSeparator: action.payload.separator,
			};
		case types.SET_TIME_ZONE:
			return {
				...state,
				timeZone: action.payload.timeZone,
			};
		case types.SET_TIMEZONE_LABEL:
			return {
				...state,
				timeZoneLabel: action.payload.label,
			};
		case types.SET_TIMEZONE_VISIBILITY:
			return {
				...state,
				showTimeZone: action.payload.show,
			};
		case types.SET_DATE_INPUT_VISIBILITY:
			return {
				...state,
				showDateInput: action.payload.show,
			};
		default:
			return state;
	}
};
