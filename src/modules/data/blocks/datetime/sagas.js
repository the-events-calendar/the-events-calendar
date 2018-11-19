/**
 * External Dependencies
 */
import { put, takeEvery, select, takeLatest, call, all } from 'redux-saga/effects';
import { delay } from 'redux-saga';

/**
 * Internal dependencies
 */
import {
	types,
	selectors,
	actions,
	thunks,
} from '@moderntribe/events/data/blocks/datetime';
import { date as dateUtil, moment as momentUtil, time as timeUtil } from '@moderntribe/common/utils';

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
		start: yield call( timeUtil.toSeconds, moments.start.format( timeUtil.TIME_FORMAT_HH_MM_SS ) ),
		end: yield call( timeUtil.toSeconds, moments.end.format( timeUtil.TIME_FORMAT_HH_MM_SS ) ),
	} );
}

/**
 * Convert current start and end seconds into time object
 *
 * @export
 * @returns {Object} {start, end}
 */
export function* deriveTimeFromSeconds( { start, end } ) {
	const startTime = yield call( timeUtil.fromSeconds, start, timeUtil.TIME_FORMAT_HH_MM_SS );
	const endTime = yield call( timeUtil.fromSeconds, end, timeUtil.TIME_FORMAT_HH_MM_SS );

	const [ startHour, startMinute, startSecond ] = yield call( [ startTime, 'split' ], ':' );
	const [ endHour, endMinute, endSecond ] = yield call( [ endTime, 'split' ], ':' );

	return {
		start: {
			hour: startHour,
			minute: startMinute,
			second: startSecond,
		},
		end: {
			hour: endHour,
			minute: endMinute,
			second: endSecond,
		},
	};
}

/**
 * Prevents end time from being before start time.
 * Should only prevent when not a multi-day event.
 *
 * @export
 * @param {Object} { actions } Actions for syncing
 * @param {Object} { startTime, endTime } Start and end time
 * @param {Object} action Action received
 */
export function* preventEndTimeBeforeStartTime() {
	const seconds = yield call( deriveSecondsFromDates );

	// 	// If end time is earlier than start time, fix time
	if ( seconds.end <= seconds.start ) {
		// If there is less than half an hour left in the day, roll back one hour
		if ( seconds.start + HALF_HOUR_IN_SECONDS >= DAY_IN_SECONDS ) {
			seconds.start -= HOUR_IN_SECONDS;
		}

		seconds.end = seconds.start + HALF_HOUR_IN_SECONDS;

		const time = yield call( deriveTimeFromSeconds, seconds );
		const moments = yield call( deriveMomentsFromDates );
		const dates = {
			start: moments.start.set( time.start ),
			end: moments.end.set( time.end ),
		};

		yield put( thunks.setDateTime( dates ) );
	}
}

/**
 * Prevents start time from appearing ahead of end time.
 * Should only prevent when not a multi-day event.
 *
 * @export
 * @param {Object} { actions } Actions for syncing
 * @param {Object} { startTime, endTime } Start and end time
 * @param {Object} action Action received
 */
export function* preventStartTimeAfterEndTime() {
	const seconds = yield call( deriveSecondsFromDates );

	if ( seconds.start >= seconds.end ) {
		seconds.start = Math.max( seconds.end - HALF_HOUR_IN_SECONDS, 0 );
		seconds.end = Math.max( seconds.start + MINUTE_IN_SECONDS, seconds.end );

		const time = yield call( deriveTimeFromSeconds, seconds );
		const moments = yield call( deriveMomentsFromDates );
		const dates = {
			start: moments.start.set( time.start ),
			end: moments.end.set( time.end ),
		};

		yield put( thunks.setDateTime( dates ) );
	}
}

export function* handleStartDateTimeChange( action ) {
	yield call( setHumanReadableFromDate, action );
	yield call( preventEndTimeBeforeStartTime );
}

export function* handleEndDateTimeChange( action ) {
	yield call( setHumanReadableFromDate, action );
	yield call( preventStartTimeAfterEndTime );
}

/**
 * Watchers of actions and act accordingly to each.
 *
 * @since 0.3.1-alpha
 *
 * @returns {IterableIterator<*|ForkEffect>}
 */
export default function* watchers() {
	yield takeEvery( types.SET_START_DATE_TIME, handleStartDateTimeChange );
	yield takeEvery( types.SET_END_DATE_TIME, handleEndDateTimeChange );
	yield takeEvery( types.SET_TIME_ZONE, onTimeZoneChange );
	yield takeLatest( types.SET_NATURAL_LANGUAGE_LABEL, onHumanReadableChange );
}
