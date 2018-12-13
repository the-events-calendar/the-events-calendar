/**
 * External Dependencies
 */
import { call, put, all, takeEvery } from 'redux-saga/effects';

/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import * as types from './types';
import { DEFAULT_STATE } from './reducer';
import * as actions from './actions';
import { priceSettings } from '@moderntribe/common/utils/globals';

export function* setInitialState( action ) {
	const { get } = action.payload;
	const settings = yield call( priceSettings );
	const isNewEvent = yield call( [ select( 'core/editor' ), 'isEditedPostNew' ] );

	const currencySymbol = isNewEvent
		? settings.defaultCurrencySymbol
		: get( 'currencySymbol', DEFAULT_STATE.symbol );

	const currencyPosition = isNewEvent
		? settings.defaultCurrencyPosition
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
