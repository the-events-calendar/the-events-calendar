/**
 * External dependencies
 */
import { combineReducers } from 'redux';
import { uniq } from 'lodash';

/**
 * Internal dependencies
 */
import * as types from './../types';

/**
 * Reducer that handles adding and removing blocks from state.
 *
 * This sets the state.events.blocks.venue.blocks.byId state, which holds all venue blocks indexed by clientId.
 *
 * @since 6.2.0
 * @param {Object} state  State object.
 * @param {string} action Action being taken.
 * @return {{}} The new state.
 */
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

/**
 * Reducer that handles adding and removing blocks from state.
 *
 * This sets the state.events.blocks.venue.blocks.allIds state, which is a numerically indexed array of all venues.
 *
 * @since 6.2.0
 * @param {Object} state  State object.
 * @param {string} action Action being taken.
 * @return {{}} The new state.
 */
export const allIds = ( state = [], action ) => {
	switch ( action.type ) {
		case types.ADD_BLOCK_VENUE:
			return uniq( [ ...state, action.payload.venue ] );
		case types.REMOVE_BLOCK_VENUE:
			return state.filter( ( venue ) => venue !== action.payload.venue );
		default:
			return state;
	}
};

/**
 * Reducer that handles setting the showMap and showMapLink values in state.
 *
 * This sets the state.events.blocks.venue.blocks.core state.
 *
 * @since 6.2.0
 * @param {Object} state  State object.
 * @param {string} action Action being taken.
 * @return {{}} The new state.
 */
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
};

export default combineReducers( {
	core,
	byId,
	allIds,
} );
