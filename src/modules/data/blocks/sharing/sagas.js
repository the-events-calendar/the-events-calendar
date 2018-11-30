/**
 * External Dependencies
 */
import { put, all, takeEvery } from 'redux-saga/effects';

/**
 * Internal dependencies
 */
import * as types from './types';
import { DEFAULT_STATE } from './reducer';
import * as actions from './actions';

export function* setInitialState( action ) {
	const { get } = action.payload;

	yield all( [
		put(
			actions.setGoogleCalendarLabel(
				get( 'googleCalendarLabel', DEFAULT_STATE.googleCalendarLabel )
			)
		),
		put( actions.setiCalLabel( get( 'iCalLabel', DEFAULT_STATE.iCalLabel ) ) ),
		put( actions.setHasIcal( get( 'hasiCal', DEFAULT_STATE.hasiCal ) ) ),
		put(
			actions.setHasGoogleCalendar( get( 'hasGoogleCalendar', DEFAULT_STATE.hasGoogleCalendar ) )
		),
	] );
}

export default function* watchers() {
	yield takeEvery( types.SET_INITIAL_STATE, setInitialState );
}
