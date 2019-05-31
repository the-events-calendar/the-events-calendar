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
		put( actions.setDetailsTitle( get( 'detailsTitle', DEFAULT_STATE.detailsTitle ) ) ),
		put( actions.setOrganizerTitle( get( 'organizerTitle', DEFAULT_STATE.organizerTitle ) ) ),
	] );
}

export default function* watchers() {
	yield takeEvery( types.SET_INITIAL_STATE, setInitialState );
}
