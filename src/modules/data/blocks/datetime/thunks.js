/**
 * WordPress dependencies
 */
import { applyFilters } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import {
	setStartDateTime,
	setEndDateTime,
	setAllDay as setAllDayAction,
	setMultiDay as setMultiDayAction,
	setSeparatorDate,
	setSeparatorTime,
	setTimeZone,
	setTimeZoneLabel,
	setTimeZoneVisibility,
	setNaturalLanguageLabel,
} from './actions';
import { DEFAULT_STATE } from './reducer';
import { maybeBulkDispatch } from '@moderntribe/events/data/utils';
import { date, moment, time } from '@moderntribe/common/utils';

const {
	isSameDay,
	parseFormats,
	replaceDate,
	setTimeInSeconds,
	toDateTime,
	toMoment,
	adjustStart,
} = moment;

export const setStartTime = ( { start, seconds } ) => ( dispatch ) => {
	// const startDateTime = toDateTime( setTimeInSeconds( toMoment( start ), seconds ) );
	// dispatch( setStartDateTime( startDateTime ) );
};

export const setEndTime = ( { end, seconds } ) => ( dispatch ) => {
	// const endDateTime = toDateTime( setTimeInSeconds( toMoment( end ), seconds ) );
	// dispatch( setEndDateTime( endDateTime ) );
};

export const setAllDay = ( { start, end, isAllDay } ) => ( dispatch ) => {
	// if ( isAllDay ) {
	// 	const startDateTime = toDateTime( setTimeInSeconds( toMoment( start ), 0 ) );
	// 	const endDateTime = toDateTime( setTimeInSeconds( toMoment( end ), time.DAY_IN_SECONDS - 1 ) );
	// 	dispatch( setStartDateTime( startDateTime ) );
	// 	dispatch( setEndDateTime( endDateTime ) );
	// }

	// dispatch( setAllDayAction( isAllDay ) );
};

export const setDates = ( { start, end, from, to } ) => ( dispatch ) => {
	// const startMoment = toMoment( start );
	// const endMoment = toMoment( end );

	// const result = adjustStart(
	// 	replaceDate( startMoment, toMoment( from ) ),
	// 	replaceDate( endMoment, toMoment( to || from ) ),
	// );

	// dispatch( setStartDateTime( toDateTime( result.start ) ) );
	// dispatch( setEndDateTime( toDateTime( result.end ) ) );
};

export const setDateTime = ( { start, end } ) => ( dispatch ) => {
	// const result = adjustStart(
	// 	toMoment( start ),
	// 	toMoment( end || start ),
	// );

	// const isMultiDay = ! isSameDay( result.start, result.end );
	// dispatch( setStartDateTime( toDateTime( result.start ) ) );
	// dispatch( setEndDateTime( toDateTime( result.end ) ) );
	// dispatch( setMultiDayAction( isMultiDay ) );
};

export const setMultiDay = ( { start, end, isMultiDay } ) => ( dispatch ) => {
	// if ( isMultiDay ) {
	// 	const RANGE_DAYS = applyFilters( 'tec.datetime.defaultRange', 3 );
	// 	const endMoment = toMoment( end ).clone().add( RANGE_DAYS, 'days' );
	// 	dispatch( setEndDateTime( toDateTime( endMoment ) ) );
	// } else {
	// 	const startMoment = toMoment( start );
	// 	const result = adjustStart(
	// 		startMoment,
	// 		replaceDate( toMoment( end ), startMoment ),
	// 	);

	// 	dispatch( setStartDateTime( toDateTime( result.start ) ) );
	// 	dispatch( setEndDateTime( toDateTime( result.end ) ) );
	// }

	// dispatch( setMultiDayAction( isMultiDay ) );
};

export const setInitialState = ( { get, attributes } ) => ( dispatch ) => {
	const timeZone = get( 'timeZone', DEFAULT_STATE.timeZone );
	const defaultTimeZone = get( 'timeZoneLabel', timeZone );

	maybeBulkDispatch( attributes, dispatch )( [
		[ setStartDateTime, 'start', DEFAULT_STATE.start ],
		[ setEndDateTime, 'end', DEFAULT_STATE.end ],
		[ setAllDayAction, 'allDay', DEFAULT_STATE.allDay ],
		[ setSeparatorDate, 'separatorDate', DEFAULT_STATE.dateTimeSeparator ],
		[ setSeparatorTime, 'separatorTime', DEFAULT_STATE.timeRangeSeparator ],
		[ setTimeZone, 'timeZone', DEFAULT_STATE.timeZone ],
		[ setTimeZoneLabel, 'timeZoneLabel', defaultTimeZone ],
		[ setTimeZoneVisibility, 'showTimeZone', DEFAULT_STATE.showTimeZone ],
	] );

	const values = {
		start: DEFAULT_STATE.start,
		end: DEFAULT_STATE.end,
	};

	if ( attributes.start ) {
		values.start = toDateTime( parseFormats( attributes.start ) );
		dispatch( setStartDateTime( values.start ) );
	}

	if ( attributes.end ) {
		values.end = toDateTime( parseFormats( attributes.end ) );
		dispatch( setEndDateTime( values.end ) );
	}

	dispatch( setNaturalLanguageLabel( date.rangeToNaturalLanguage( values.start, values.end ) ) );

	dispatch( setMultiDayAction( ! isSameDay( values.start, values.end ) ) );
};
