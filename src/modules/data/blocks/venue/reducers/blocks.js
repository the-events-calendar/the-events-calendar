/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import * as types from './../types';

export const byId = ( state = {}, action ) => {
	switch ( action.type ) {
		case types.ADD_BLOCK_VENUE:
			return {
				...state,
				[ action.payload.id ]: action.payload.venue,
			};
		case types.REMOVE_BLOCK_VENUE:
			return Object.keys( state ).reduce( ( newState, id ) => {
				if ( id === action.payload.id ) {
					return newState;
				}

				return {
					...newState,
					[ id ]: state[ id ],
				};
			}, {} );
		default:
			return state;
	}
};

export const allIds = ( state = [], action ) => {
	switch ( action.type ) {
		case types.ADD_BLOCK_VENUE:
			return [ ...state, action.payload.venue ];
		case types.REMOVE_BLOCK_VENUE:
			return state.filter( venue => venue !== action.payload.venue );
		default:
			return state;
	}
};

export default combineReducers( {
	byId,
	allIds,
} );
