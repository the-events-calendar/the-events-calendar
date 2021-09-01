/**
 * External dependencies
 */
import { uniq } from 'lodash';

/**
 * Internal dependencies
 */
import * as types from './../types';

export const DEFAULT_STATE = [];

export const setInitialState = ( data ) => {
	const { meta } = data;
	if ( meta.hasOwnProperty( '_EventOrganizerID' ) ) {
		DEFAULT_STATE.push( ...meta._EventOrganizerID );
	}
};

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
