/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import moment from 'moment/moment';

/**
 * Internal dependencies
 */
import {
	globals,
	date,
	moment as momentUtil,
	time,
} from '@moderntribe/common/utils';
import * as types from './types';

const defaultStartTime = globals.defaultTimes().start ? globals.defaultTimes().start : '08:00:00';
const defaultEndTime = globals.defaultTimes().end ? globals.defaultTimes().end : '17:00:00';
const defaultStartTimeSeconds = time.toSeconds( defaultStartTime, time.TIME_FORMAT_HH_MM_SS );
const defaultEndTimeSeconds = time.toSeconds( defaultEndTime, time.TIME_FORMAT_HH_MM_SS );

export const defaultStartMoment = moment().startOf( 'day' ).seconds( defaultStartTimeSeconds );
export const defaultEndMoment = moment().startOf( 'day' ).seconds( defaultEndTimeSeconds );

export const DEFAULT_STATE = {
	start: momentUtil.toDateTime( defaultStartMoment ),
	end: momentUtil.toDateTime( defaultEndMoment ),
	startTimeInput: momentUtil.toTime( defaultStartMoment ),
	endTimeInput: momentUtil.toTime( defaultEndMoment ),
	naturalLanguage: '',
	dateTimeSeparator: globals.settings().dateTimeSeparator
		? globals.settings().dateTimeSeparator
		: __( '@', 'the-events-calendar' ),
	timeRangeSeparator: globals.settings().timeRangeSeparator
		? globals.settings().timeRangeSeparator
		: __( '-', 'the-events-calendar' ),
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
		case types.SET_START_TIME_INPUT:
			return {
				...state,
				startTimeInput: action.payload.startTimeInput,
			};
		case types.SET_END_TIME_INPUT:
			return {
				...state,
				endTimeInput: action.payload.endTimeInput,
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
