/**
 * External dependencies
 */
import { combineReducers } from 'redux';
import { uniq } from 'lodash';

/**
 * Internal dependencies
 */
import * as types from './../types';
import { store } from '@moderntribe/common/store';
const { getState, dispatch } = store;

export const byId = ( state = {}, action ) => {
	switch ( action.type ) {
		case types.ADD_BLOCK_VENUE:
			const venues = state;
			Object.keys( venues ).forEach( ( key ) => {
				state[ key ] = action.payload.venue;
			} )
			return {
				...venues,
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
			const venues = [ action.payload.venue ];
			return venues;
			//return uniq( [ ...state, action.payload.venue ] );
		case types.REMOVE_BLOCK_VENUE:
			return state.filter( venue => venue !== action.payload.venue );
		default:
			return state;
	}
};

export const core = ( state = {}, action ) => {
	switch ( action.type ) {
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
