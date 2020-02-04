import { uniq } from 'lodash';

/**
 * Internal dependencies
 */
import * as types from './../types';

export const setInitialState = ( entityRecord ) => {
	DEFAULT_STATE.push( ...entityRecord.meta._EventOrganizerID );
};

export const DEFAULT_STATE = [];

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.ADD_CLASSIC_ORGANIZERS:
			return uniq( [ ...state, action.payload.organizer ] );
		case types.REMOVE_CLASSIC_ORGANIZERS:
			return state.filter( ( organizer ) => organizer !== action.payload.organizer );
		case types.SET_CLASSIC_ORGANIZERS:
			return [ ...action.payload.organizers ];
		default:
			return state;
	}
};
