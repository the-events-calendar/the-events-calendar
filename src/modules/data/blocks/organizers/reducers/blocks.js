/**
 * External dependencies
 */
import { uniq } from 'lodash';

/**
 * Internal dependencies
 */
import * as types from './../types';

export const DEFAULT_STATE = [];

export default ( state = [], action ) => {
	switch ( action.type ) {
		case types.ADD_BLOCK_ORGANIZER:
			return uniq( [ ...state, action.payload.organizer ] );
		case types.REMOVE_BLOCK_ORGANIZER:
			return state.filter( ( organizer ) => organizer !== action.payload.organizer );
		default:
			return state;
	}
};
