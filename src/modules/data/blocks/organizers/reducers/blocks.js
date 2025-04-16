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
		case types.ADD_BLOCK_ORGANIZER:
			return {
				...state,
				[ action.payload.id ]: action.payload.organizer,
			};
		case types.REMOVE_BLOCK_ORGANIZER:
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
		case types.ADD_BLOCK_ORGANIZER:
			return [ ...state, action.payload.organizer ];
		case types.REMOVE_BLOCK_ORGANIZER:
			return state.filter( ( organizer ) => organizer !== action.payload.organizer );
		default:
			return state;
	}
};

export default combineReducers( {
	byId,
	allIds,
} );
