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
	const venue = get( 'venue' ) ? get( 'venue' ) : DEFAULT_STATE.venue;

	yield all( [
		put( actions.setVenue( venue ) ),
		put( actions.setShowMap( get( 'showMap', DEFAULT_STATE.showMap ) ) ),
		put( actions.setShowMapLink( get( 'showMapLink', DEFAULT_STATE.showMapLink ) ) ),
	] );
}

export default function* watchers() {
	yield takeEvery( types.SET_INITIAL_STATE, setInitialState );
}
