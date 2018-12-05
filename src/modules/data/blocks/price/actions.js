/**
 * Internal dependencies
 */
import * as types from './types';

export const setInitialState = ( props ) => ( {
	type: types.SET_INITIAL_STATE,
	payload: props,
} );

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

export const togglePosition = ( showBefore ) => {
	return setPosition( showBefore ? 'prefix' : 'suffix' );
};

export const setSymbol = ( symbol ) => ( {
	type: types.SET_PRICE_SYMBOL,
	payload: {
		symbol,
	},
} );

export const setDescription = ( description ) => ( {
	type: types.SET_PRICE_DESCRIPTION,
	payload: {
		description,
	},
} );
