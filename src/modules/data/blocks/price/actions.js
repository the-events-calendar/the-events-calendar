/**
 * Internal dependencies
 */
import * as types from './types';

export const setCost = ( cost ) => ( {
	type: types.SET_PRICE_COST,
	payload: {
		cost,
	},
} );

export const setPosition = ( position ) => ( {
	type: types.SET_PRICE_POSITION,
	payload: {
		position,
	},
} );

export const setSymbol = ( symbol ) => ( {
	type: types.SET_PRICE_SYMBOL,
	payload: {
		symbol,
	},
} );

export const setCode = ( code ) => ( {
	type: types.SET_PRICE_CODE,
	payload: {
		code,
	},
} );
