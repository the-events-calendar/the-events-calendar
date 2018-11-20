/**
 * External Dependencies
 */
import { put, take, select, takeLatest, call, all } from 'redux-saga/effects';
import { delay } from 'redux-saga';
import { applyFilters } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import {
	types,
	selectors,
	actions,
	thunks,
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
 * Fired when the Human Readable has a change() event fired on the input, in combination with takeLatest and delay
 * simulates the debounce functionality as gets executed every WAIT_PERIOD_IN_MILLISECONDS to prevent doing work
 * when the input is handling a new change() events
 *
 * @since 0.3.1-alpha
 *
 * @returns {IterableIterator<*>}
 */
export function* onHumanReadableChange() {
	// Wait in case there's a new change on the input
	yield call( delay, 700 );

	const label = yield select( selectors.getNaturalLanguageLabel );
	const dates = dateUtil.labelToDate( label );

	if ( dates.start === null && dates.end === null ) {
		yield call( resetNaturalLanguageLabel );
	} else {
		yield put( thunks.setDateTime( dates ) );
	}
}

/**
 * Set timezone label on timezone change
 *
 * @since 0.3.5-alpha
 */
export function* onTimeZoneChange( action ) {
	yield put( actions.setTimeZoneLabel( action.payload.timeZone ) );
}

/**
 * Convert current start and end date into seconds
 *
 * @export
 * @returns {Object} {start, end}
 */
export function* deriveMomentsFromDates() {
	const dates = yield all( {
		start: select( selectors.getStart ),
		end: select( selectors.getEnd ),
	} );

	return yield all( {
		start: yield call( momentUtil.toMoment, dates.start ),
		end: yield call( momentUtil.toMoment, dates.end ),
	} );
}

/**
 * Convert current start and end date into seconds
 *
 * @export
 * @returns {Object} {start, end}
 */
export function* deriveSecondsFromDates() {
	const moments = yield call( deriveMomentsFromDates );

	return yield all( {
		start: yield call( timeUtil.toSeconds, moments.start.format( 'HH:mm:ss' ) ),
		end: yield call( timeUtil.toSeconds, moments.end.format( 'HH:mm:ss' ) ),
	} );
}

/**
 * Prevents end time from being before start time.
 * Should only prevent when not a multi-day event.
 *
 * @export
 */
export function* preventEndTimeBeforeStartTime( action ) {
	const isMultiDay = yield select( selectors.getMultiDay );
	if ( isMultiDay ) {
		return;
	}

	const seconds = yield call( deriveSecondsFromDates );

	if ( [ types.SET_END_TIME, types.SET_START_TIME ].includes( action.type ) ) {
		// Update seconds to use payload
		// NOTE: Mutation
		yield call( [ Object, 'assign' ], seconds, action.payload );
	}

	// 	// If end time is earlier than start time, fix time
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
 */
export function* preventStartTimeAfterEndTime( action ) {
	const isMultiDay = yield select( selectors.getMultiDay );
	if ( isMultiDay ) {
		return;
	}

	const seconds = yield call( deriveSecondsFromDates );
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

export function* handleStartTimeChange( action ) {
	if ( action.payload.start === 'all-day' ) {
		yield call( setAllDay );
	} else {
		const { start } = yield call( deriveMomentsFromDates );
		// NOTE: Mutation
		yield call( momentUtil.setTimeInSeconds, start, action.payload.start );
		const startDate = yield call( momentUtil.toDateTime, start );
		yield put( actions.setStartDateTime( startDate ) );
	}
}

export function* handleEndTimeChange( action ) {
	if ( action.payload.end === 'all-day' ) {
		yield call( setAllDay );
	} else {
		const { end } = yield call( deriveMomentsFromDates );
		// NOTE: Mutation
		yield call( momentUtil.setTimeInSeconds, end, action.payload.end );
		const endDate = yield call( momentUtil.toDateTime, end );
		yield put( actions.setEndDateTime( endDate ) );
	}
}

export function* handleStartDateTimeChange( action ) {
	yield call( setHumanReadableFromDate, action );
	yield call( preventEndTimeBeforeStartTime, action );
}

export function* handleEndDateTimeChange( action ) {
	yield call( setHumanReadableFromDate, action );
	yield call( preventStartTimeAfterEndTime, action );
}

export function* handler( action ) {
	switch ( action.type ) {
		case types.SET_TIME_ZONE:
			yield call( onTimeZoneChange, action );
			break;

		case types.SET_START_DATE_TIME:
			yield call( handleStartDateTimeChange, action );
			break;

		case types.SET_END_DATE_TIME:
			yield call( handleEndDateTimeChange, action );
			break;

		case types.SET_START_TIME:
			yield call( handleStartTimeChange, action );
			yield call( preventEndTimeBeforeStartTime, action );
			break;

		case types.SET_END_TIME:
			yield call( handleEndTimeChange, action );
			yield call( preventStartTimeAfterEndTime, action );
			break;

		case types.SET_MULTI_DAY:
			yield call( handleMultiDay, action );
			break;

		default:
			break;
	}
}

/**
 * Watchers of actions and act accordingly to each.
 *
 * @since 0.3.1-alpha
 *
 * @returns {IterableIterator<*|ForkEffect>}
 */
export default function* watchers() {
	yield takeLatest( types.SET_NATURAL_LANGUAGE_LABEL, onHumanReadableChange );

	while ( true ) {
		const action = yield take( [
			types.SET_START_DATE_TIME,
			types.SET_END_DATE_TIME,
			types.SET_START_TIME,
			types.SET_END_TIME,
			types.SET_MULTI_DAY,
			types.SET_TIME_ZONE,
		] );
		yield call( handler, action );
	}
}
