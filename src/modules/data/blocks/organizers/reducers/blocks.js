/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import * as types from './../types';
import block from './block';

export const byId = ( state = {}, action ) => {
	switch ( action.type ) {
		case types.ADD_ORGANIZER_BLOCK:
		case types.REMOVE_ORGANIZER_BLOCK:
			return {
				...state,
				[ action.payload.id ]: block( state[ action.payload.id ], action ),
			};
		default:
			return state;
	}
};

export const allIds = ( state = [], action ) => {
	switch ( action.type ) {
		case types.ADD_ORGANIZER_BLOCK:
			return [ ...state, action.payload.organizer ];
		case types.REMOVE_ORGANIZER_BLOCK:
			return [ ...state ].filter( ( organizer ) => organizer !== action.payload.organizer );
		default:
			return state;
	}
};

export default combineReducers( {
	byId,
	allIds,
} );
