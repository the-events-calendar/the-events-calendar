/**
 * Internal dependencies
 */
import * as types from './types';
import * as utils from './utils';

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
	return setPosition( utils.getPosition( showBefore ) );
};

export const setSymbol = ( symbol ) => ( {
	type: types.SET_PRICE_SYMBOL,
	payload: {
		symbol,
	},
} );
