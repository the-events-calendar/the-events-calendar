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
		case types.SET_VENUE:
			return {
				[ action.payload.id ]: action.payload.venue
			};
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
		case types.SET_VENUE:
			return [ action.payload.venue ];
		case types.ADD_BLOCK_VENUE:
			return [ ...state, action.payload.venue ];
		case types.REMOVE_BLOCK_VENUE:
			return state.filter( venue => venue !== action.payload.venue );
		default:
			return state;
	}
};

export const core = ( state = {}, action ) => {
	switch ( action.type ) {
		case types.SET_VENUE:
			return {
				...state,
				venue: action.payload.venue,
			};
		case types.SET_VENUE_MAP:
			return {
				...state,
				showMap: action.payload.showMap,
			};
		case types.SET_VENUE_MAP_LINK:
			return {
				...state,
				showMapLink: action.payload.showMapLink,
			};
		default:
			return state;
	}
}

export default combineReducers( {
	core,
	byId,
	allIds,
} );
