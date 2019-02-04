/**
 * External Dependencies
 */
import { put, take, select, call, all } from 'redux-saga/effects';
import { applyFilters } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import {
	types,
	selectors,
	actions,
} from '@moderntribe/events/data/blocks/datetime';
import {
	date as dateUtil,
	moment as momentUtil,
	time as timeUtil,
} from '@moderntribe/common/utils';

const {
	HALF_HOUR_IN_SECONDS,
	DAY_IN_SECONDS,
	HOUR_IN_SECONDS,
	MINUTE_IN_SECONDS,
} = timeUtil;

//
// ──────────────────────────────────────────────────── I ──────────
//   :::::: D E R I V E : :  :   :    :     :        :          :
// ──────────────────────────────────────────────────────────────
//

/**
 * Convert current start and end date into moments
 *
 * @export
 * @since 4.7
 * @returns {Object} {start, end}
 */
export function* deriveMomentsFromDates() {
	const dates = yield all( {
		start: select( selectors.getStart ),
		end: select( selectors.getEnd ),
	} );

	return yield all( {
		start: call( momentUtil.toMoment, dates.start ),
		end: call( momentUtil.toMoment, dates.end ),
	} );
}

/**
 * Convert current start and end date into seconds
 *
 * @export
 * @since 4.7
 * @returns {Object} {start, end}
 */
export function* deriveSecondsFromDates() {
	const moments = yield call( deriveMomentsFromDates );

	const time = yield all( {
		start: call( momentUtil.toDatabaseTime, moments.start ),
		end: call( momentUtil.toDatabaseTime, moments.end ),
	} );

	return yield all( {
		start: call( timeUtil.toSeconds, time.start ),
		end: call( timeUtil.toSeconds, time.end ),
	} );
}

//
// ──────────────────────────────────────────────────────────────────── II ──────────
//   :::::: H U M A N   R E A D A B L E : :  :   :    :     :        :          :
// ──────────────────────────────────────────────────────────────────────────────
//

/**
 * Set the human readable label into the store, based on a start and end date to generate a new label based on those
 *
 * @since 0.3.1-alpha
 *
 * @param {object} dates An object that represents the start / end date
 * @returns {IterableIterator<*>}
 */
export function* setHumanReadableLabel( dates = {} ) {
	const currentLabel = yield select( selectors.getNaturalLanguageLabel );

	if ( currentLabel === '' ) {
		return;
	}

	const updatedLabel = yield call( dateUtil.rangeToNaturalLanguage, dates.start, dates.end );

	if ( currentLabel !== updatedLabel ) {
		yield put( actions.setNaturalLanguageLabel( updatedLabel ) );
	}
}

/**
 * Set the humman readable label from an action that sets the date on the event either the start or end date
 * first selects as default value current start and end dates after that based on the action type selects the
 * date to be replaced or the new date to be set before doing the conversion into text.
 *
 * @since 0.3.1-alpha
 *
 * @param {object} action Dispateched by the component and watched by this generator
 * @returns {IterableIterator<*>}
 */
export function* setHumanReadableFromDate( action ) {
	const dates = {
		start: yield select( selectors.getStart ),
		end: yield select( selectors.getEnd ),
	};

	if ( action.type === types.SET_END_DATE_TIME ) {
		dates.end = action.payload.end;
	} else {
		dates.start = action.payload.start;
	}
	yield call( setHumanReadableLabel, dates );
}

/**
 * Generator used to reset the label of the input using the current values on the start and end date, useful when
 * the user enters a data that is not valid or can't be parsed into a valid date so the input always reflects a valid
 * date based on the current values set into the dates of the event.
 *
 * @since 0.3.1-alpha
 *
 * @returns {IterableIterator<*>}
 */
export function* resetNaturalLanguageLabel() {
	const dates = yield all( {
		start: select( selectors.getStart ),
		end: select( selectors.getEnd ),
	} );
	yield call( setHumanReadableLabel, dates );
}

/**
 * Fired when the Human Readable has a change() event fired on the input
 * when the input is handling a new change() events
 *
 * @since 0.3.1-alpha
 *
 * @returns {IterableIterator<*>}
 */
export function* onHumanReadableChange() {
	const label = yield select( selectors.getNaturalLanguageLabel );
	const { start, end } = yield call( dateUtil.labelToDate, label );

	if ( start === null && end === null ) {
		yield call( resetNaturalLanguageLabel );
	} else {
		const moments = yield all( {
			start: call( momentUtil.toMoment, start ),
			end: call( momentUtil.toMoment, end || start ),
		} );

		const result = yield call( momentUtil.adjustStart, moments.start, moments.end );

		const isMultiDay = ! ( yield call( momentUtil.isSameDay, result.start, result.end ) );

		const isAllDay = ! isMultiDay && ( '00:00' === moments.start.format( 'HH:mm' ) && '23:59' === moments.end.format( 'HH:mm' ) );

		const dates = yield all( {
			start: call( momentUtil.toDateTime, result.start ),
			end: call( momentUtil.toDateTime, result.end ),
		} );

		yield all( [
			put( actions.setStartDateTime( dates.start ) ),
			put( actions.setEndDateTime( dates.end ) ),
			put( actions.setMultiDay( isMultiDay ) ),
			put( actions.setAllDay( isAllDay ) ),
		] );
	}
}

//
// ────────────────────────────────────────────────────────────────────── III ──────────
//   :::::: C H A N G E   H A N D L E R S : :  :   :    :     :        :          :
// ────────────────────────────────────────────────────────────────────────────────
//

/**
 * Set timezone label on timezone change
 *
 * @since 0.3.5-alpha
 * @param {Object} action Payload with timeZone
 */
export function* onTimeZoneChange( action ) {
	yield put( actions.setTimeZoneLabel( action.payload.timeZone ) );
}

/**
 * Handle date range changes on calendar
 *
 * @export
 * @since 4.7
 * @param {Object} action Payload with to and from
 */
export function* handleDateRangeChange( action ) {
	const { to, from } = action.payload;
	const moments = yield call( deriveMomentsFromDates );

	const rangeMoments = yield all( {
		from: call( momentUtil.toMoment, from ),
		to: call( momentUtil.toMoment, to || from ),
	} );

	// NOTE: Mutation
	yield all( {
		start: call( momentUtil.replaceDate, moments.start, rangeMoments.from ),
		end: call( momentUtil.replaceDate, moments.end, rangeMoments.to ),
	} );

	const result = yield call( momentUtil.adjustStart, moments.start, moments.end );

	const dates = yield all( {
		start: call( momentUtil.toDateTime, result.start ),
		end: call( momentUtil.toDateTime, result.end ),
	} );

	yield all( [
		put( actions.setStartDateTime( dates.start ) ),
		put( actions.setEndDateTime( dates.end ) ),
	] );
}

/**
 * Prevents end time from being before start time.
 * Should only prevent when not a multi-day event.
 *
 * @export
 * @since 4.7
 * @param {Object} action Payload with seconds in start or end key (when time change)
 */
export function* preventEndTimeBeforeStartTime( action ) {
	const isMultiDay = yield select( selectors.getMultiDay );
	// Do not proceed when multi-day
	if ( isMultiDay ) {
		return;
	}

	const seconds = yield call( deriveSecondsFromDates );
	// Prevent only date changes from using payload
	if ( [ types.SET_END_TIME, types.SET_START_TIME ].includes( action.type ) ) {
		// Update seconds to use payload
		// NOTE: Mutation
		yield call( [ Object, 'assign' ], seconds, action.payload );
	}

	// If end time is earlier than start time, fix time
	if ( seconds.end <= seconds.start ) {
		// If there is less than half an hour left in the day, roll back one hour
		if ( seconds.start + HALF_HOUR_IN_SECONDS >= DAY_IN_SECONDS ) {
			seconds.start -= HOUR_IN_SECONDS;
		}

		seconds.end = seconds.start + HALF_HOUR_IN_SECONDS;

		const moments = yield call( deriveMomentsFromDates );

		// NOTE: Mutation
		yield all( {
			start: call( momentUtil.setTimeInSeconds, moments.start, seconds.start ),
			end: call( momentUtil.setTimeInSeconds, moments.end, seconds.end ),
		} );

		const dates = yield all( {
			start: call( momentUtil.toDateTime, moments.start ),
			end: call( momentUtil.toDateTime, moments.end ),
		} );

		yield all( [
			put( actions.setStartDateTime( dates.start ) ),
			put( actions.setEndDateTime( dates.end ) ),
		] );
	}
}

/**
 * Prevents start time from appearing ahead of end time.
 * Should only prevent when not a multi-day event.
 *
 * @export
 * @since 4.7
 * @param {Object} action Payload with seconds in start or end key (when time change)
 */
export function* preventStartTimeAfterEndTime( action ) {
	const isMultiDay = yield select( selectors.getMultiDay );
	// Do not proceed when multi-day
	if ( isMultiDay ) {
		return;
	}

	const seconds = yield call( deriveSecondsFromDates );

	// Prevent only date changes from using payload
	if ( [ types.SET_END_TIME, types.SET_START_TIME ].includes( action.type ) ) {
		// Update seconds to use payload
		// NOTE: Mutation
		yield call( [ Object, 'assign' ], seconds, action.payload );
	}

	if ( seconds.start >= seconds.end ) {
		seconds.start = Math.max( seconds.end - HALF_HOUR_IN_SECONDS, 0 );
		seconds.end = Math.max( seconds.start + MINUTE_IN_SECONDS, seconds.end );

		const moments = yield call( deriveMomentsFromDates );

		// NOTE: Mutation
		yield all( {
			start: call( momentUtil.setTimeInSeconds, moments.start, seconds.start ),
			end: call( momentUtil.setTimeInSeconds, moments.end, seconds.end ),
		} );

		const dates = yield all( {
			start: call( momentUtil.toDateTime, moments.start ),
			end: call( momentUtil.toDateTime, moments.end ),
		} );

		yield all( [
			put( actions.setStartDateTime( dates.start ) ),
			put( actions.setEndDateTime( dates.end ) ),
		] );
	}
}
/**
 * Handles all-day payloads. Set start and end time to be `00:00` and `23:59`
 *
 * @export
 * @since 4.7
 */
export function* setAllDay() {
	const moments = yield call( deriveMomentsFromDates );

	// NOTE: Mutation
	yield all( {
		start: call( momentUtil.setTimeInSeconds, moments.start, 0 ),
		end: call( momentUtil.setTimeInSeconds, moments.end, timeUtil.DAY_IN_SECONDS - 1 ),
	} );

	const dates = yield all( {
		start: call( momentUtil.toDateTime, moments.start ),
		end: call( momentUtil.toDateTime, moments.end ),
	} );

	yield all( [
		put( actions.setStartDateTime( dates.start ) ),
		put( actions.setEndDateTime( dates.end ) ),
		put( actions.setAllDay( true ) ),
	] );
}

/**
 * Handles multi-day toggling
 *
 * @export
 * @since 4.7
 * @param {Object} action Payload with multiDay
 */
export function* handleMultiDay( action ) {
	const isMultiDay = action.payload.multiDay;
	const { start, end } = yield call( deriveMomentsFromDates );

	if ( isMultiDay ) {
		const RANGE_DAYS = yield call( applyFilters, 'tec.datetime.defaultRange', 3 );
		// NOTE: Mutation
		yield call( [ end, 'add' ], RANGE_DAYS, 'days' );
		const endDate = yield call( momentUtil.toDateTime, end );
		yield put( actions.setEndDateTime( endDate ) );
	} else {
		const newEnd = yield call( momentUtil.replaceDate, end, start );
		const result = yield call( momentUtil.adjustStart, start, newEnd );

		const dates = yield all( {
			start: call( momentUtil.toDateTime, result.start ),
			end: call( momentUtil.toDateTime, result.end ),
		} );

		yield all( [
			put( actions.setStartDateTime( dates.start ) ),
			put( actions.setEndDateTime( dates.end ) ),
		] );
	}
}

/**
 * Handles event start time changes
 *
 * @export
 * @since 4.7
 * @param {Object} action Payload with start of `all-day` or seconds
 */
export function* handleStartTimeChange( action ) {
	if ( action.payload.start === 'all-day' ) {
		yield call( setAllDay );
	} else {

		// Set All day to false in case they're editing.
		yield put( actions.setAllDay( false ) );

		const { start } = yield call( deriveMomentsFromDates );
		// NOTE: Mutation
		yield call( momentUtil.setTimeInSeconds, start, action.payload.start );
		const startDate = yield call( momentUtil.toDateTime, start );
		yield put( actions.setStartDateTime( startDate ) );
	}
}

/**
 * Handles event end time changes
 *
 * @export
 * @since 4.7
 * @param {Object} action Payload with end of `all-day` or seconds
 */
export function* handleEndTimeChange( action ) {
	if ( action.payload.end === 'all-day' ) {
		yield call( setAllDay );
	} else {

		// Set All day to false in case they're editing.
		yield put( actions.setAllDay( false ) );

		const { end } = yield call( deriveMomentsFromDates );
		// NOTE: Mutation
		yield call( momentUtil.setTimeInSeconds, end, action.payload.end );
		const endDate = yield call( momentUtil.toDateTime, end );
		yield put( actions.setEndDateTime( endDate ) );
	}
}

/**
 * Sets start time input
 *
 * @export
 * @since 4.7.2
 */
export function* setStartTimeInput() {
	const { start } = yield call( deriveMomentsFromDates );
	const startInput = yield call( momentUtil.toTime, start );
	yield put( actions.setStartTimeInput( startInput ) );
}

/**
 * Sets end time input
 *
 * @export
 * @since 4.7.2
 */
export function* setEndTimeInput() {
	const { end } = yield call( deriveMomentsFromDates );
	const endInput = yield call( momentUtil.toTime, end );
	yield put( actions.setEndTimeInput( endInput ) );
}

/**
 * Handle flow changes based on action type
 *
 * @export
 * @since 4.7
 * @param {Object} action Action taken
 */
export function* handler( action ) {
	switch ( action.type ) {
		case types.SET_TIME_ZONE:
			yield call( onTimeZoneChange, action );
			break;

		case types.SET_DATE_RANGE:
			yield call( handleDateRangeChange, action );
			yield call( resetNaturalLanguageLabel );
			break;

		case types.SET_START_DATE_TIME:
			yield call( preventEndTimeBeforeStartTime, action );
			yield call( setHumanReadableFromDate, action );
			break;

		case types.SET_END_DATE_TIME:
			yield call( preventStartTimeAfterEndTime, action );
			yield call( setHumanReadableFromDate, action );
			break;

		case types.SET_START_TIME:
			yield call( handleStartTimeChange, action );
			yield call( preventEndTimeBeforeStartTime, action );
			yield call( setStartTimeInput );
			yield call( resetNaturalLanguageLabel );
			break;

		case types.SET_END_TIME:
			yield call( handleEndTimeChange, action );
			yield call( preventStartTimeAfterEndTime, action );
			yield call( setEndTimeInput );
			yield call( resetNaturalLanguageLabel );
			break;

		case types.SET_MULTI_DAY:
			yield call( handleMultiDay, action );
			yield call( resetNaturalLanguageLabel );
			break;

		case types.SET_NATURAL_LANGUAGE_LABEL:
			yield call( onHumanReadableChange, action );
			break;

		default:
			break;
	}
}

//
// ──────────────────────────────────────────────────────── IV ──────────
//   :::::: W A T C H E R S : :  :   :    :     :        :          :
// ──────────────────────────────────────────────────────────────────
//

/**
 * Watchers of actions and act accordingly to each.
 *
 * @since 0.3.1-alpha
 *
 * @returns {IterableIterator<*|ForkEffect>}
 */
export default function* watchers() {
	// prevent changes from looping infinitely
	while ( true ) {
		const action = yield take( [
			types.SET_DATE_RANGE,
			types.SET_START_DATE_TIME,
			types.SET_END_DATE_TIME,
			types.SET_START_TIME,
			types.SET_END_TIME,
			types.SET_MULTI_DAY,
			types.SET_TIME_ZONE,
			types.SET_NATURAL_LANGUAGE_LABEL,
		] );
		yield call( handler, action );
	}
}
