/**
 * External dependencies
 */
import { uniq } from 'lodash';

/**
 * Internal dependencies
 */
import * as types from './../types';

export const setInitialState = ( data ) => {
	DEFAULT_STATE.push( ...data.meta._EventOrganizerID );
};

export const DEFAULT_STATE = [];

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.ADD_CLASSIC_ORGANIZERS:
			return uniq( [ ...state, action.payload.organizer ] );
		case types.REMOVE_CLASSIC_ORGANIZERS:
			return state.filter( ( organizer ) => organizer !== action.payload.organizer );
		default:
			return state;
	}
};
