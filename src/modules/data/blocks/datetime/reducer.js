/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import moment from 'moment';

/**
 * Internal dependencies
 */
import * as globals from '@moderntribe/common/utils/globals';
import * as date from '@moderntribe/common/utils/date';
import * as momentUtil from '@moderntribe/common/utils/moment';
import * as time from '@moderntribe/common/utils/time';
import * as types from './types';

const { isSameDay, parseFormats, toDateTime, toMoment, toTime } = momentUtil;

const defaultStartTime = globals.defaultTimes().start ? globals.defaultTimes().start : '08:00:00';
const defaultEndTime = globals.defaultTimes().end ? globals.defaultTimes().end : '17:00:00';
const defaultStartTimeSeconds = time.toSeconds( defaultStartTime, time.TIME_FORMAT_HH_MM_SS );
const defaultEndTimeSeconds = time.toSeconds( defaultEndTime, time.TIME_FORMAT_HH_MM_SS );
const queryStartDate = globals.postObjects().tribe_events.tribe_start_date;

export const defaultStartMoment = queryStartDate
	? moment( queryStartDate ).seconds( defaultStartTimeSeconds )
	: moment().startOf( 'day' ).seconds( defaultStartTimeSeconds );
export const defaultEndMoment = moment().startOf( 'day' ).seconds( defaultEndTimeSeconds );

const defaultStartDateTime = toDateTime( defaultStartMoment );
const defaultEndDateTime = toDateTime( defaultEndMoment );

export const DEFAULT_STATE = {
	start: defaultStartDateTime,
	end: defaultEndDateTime,
	startTimeInput: toTime( defaultStartMoment ),
	endTimeInput: toTime( defaultEndMoment ),
	naturalLanguageLabel: date.rangeToNaturalLanguage( defaultStartDateTime, defaultEndDateTime ),
	dateTimeSeparator: globals.settings().dateTimeSeparator
		? globals.settings().dateTimeSeparator
		: __( '@', 'the-events-calendar' ),
	timeRangeSeparator: globals.settings().timeRangeSeparator
		? globals.settings().timeRangeSeparator
		: __( '-', 'the-events-calendar' ),
	allDay: false,
	multiDay: false,
	timeZone: globals.timezone().timeZone
		? globals.timezone().timeZone
		: date.FORMATS.TIMEZONE.string,
	showTimeZone: false,
	isEditable: true,
};

export const defaultStateToMetaMap = {
	start: '_EventStartDate',
	end: '_EventEndDate',
	dateTimeSeparator: '_EventDateTimeSeparator',
	timeRangeSeparator: '_EventTimeRangeSeparator',
	allDay: '_EventAllDay',
	timeZone: '_EventTimezone',
};

export const setInitialState = ( data ) => {
	const { meta } = data;

	Object.keys( defaultStateToMetaMap ).forEach( ( key ) => {
		const metaKey = defaultStateToMetaMap[ key ];
		if ( meta.hasOwnProperty( metaKey ) ) {
			DEFAULT_STATE[ key ] = meta[ metaKey ];
		}
	} );

	const { start, end } = DEFAULT_STATE;

	DEFAULT_STATE.startTimeInput = toTime( parseFormats( start ) );
	DEFAULT_STATE.endTimeInput = toTime( parseFormats( end ) );
	DEFAULT_STATE.naturalLanguageLabel = date.rangeToNaturalLanguage( start, end );
	DEFAULT_STATE.multiDay = ! isSameDay(
		toMoment( start ),
		toMoment( end ),
	);
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
				naturalLanguageLabel: action.payload.label,
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
		case types.SET_TIMEZONE_VISIBILITY:
			return {
				...state,
				showTimeZone: action.payload.show,
			};
		default:
			return state;
	}
};
