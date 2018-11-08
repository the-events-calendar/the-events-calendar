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
import { date as dateUtil } from '@moderntribe/common/utils';

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
 * Watchers of actions and act accordingly to each.
 *
 * @since 0.3.1-alpha
 *
 * @returns {IterableIterator<*|ForkEffect>}
 */
export default function* watchers() {
	yield takeEvery( types.SET_START_DATE_TIME, setHumanReadableFromDate );
	yield takeEvery( types.SET_END_DATE_TIME, setHumanReadableFromDate );
	yield takeEvery( types.SET_TIME_ZONE, onTimeZoneChange );
	yield takeLatest( types.SET_NATURAL_LANGUAGE_LABEL, onHumanReadableChange );
}
