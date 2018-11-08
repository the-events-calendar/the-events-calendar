/**
 * External Dependencies
 */
import { call, put, all, takeEvery } from 'redux-saga/effects';

/**
 * Internal dependencies
 */
import * as types from './types';
import { DEFAULT_STATE } from './reducer';
import * as actions from './actions';
import { isTruthy } from '@moderntribe/common/utils/string';
import { priceSettings } from '@moderntribe/common/utils/globals';

export function* setInitialState( action ) {
	const { get } = action.payload;
	const settings = yield call( priceSettings );
	const isNewEvent = yield call( isTruthy, settings.is_new_event );

	const currencySymbol = isNewEvent
		? settings.default_currency_symbol
		: get( 'currencySymbol', DEFAULT_STATE.symbol );

	const currencyPosition = isNewEvent
		? settings.default_currency_position
		: get( 'currencyPosition', DEFAULT_STATE.position );

	yield all( [
		put( actions.setPosition( currencyPosition ) ),
		put( actions.setSymbol( currencySymbol ) ),
		put( actions.setCost( get( 'cost', DEFAULT_STATE.cost ) ) ),
		put( actions.setDescription( get( 'costDescription', DEFAULT_STATE.description ) ) ),
	] );
}

export default function* watchers() {
	yield takeEvery( types.SET_INITIAL_STATE, setInitialState );
}
