import { uniq } from 'lodash';
/**
 * Internal dependencies
 */
import * as types from './../types';

export default ( state = [], action ) => {
	switch ( action.type ) {
		case types.ADD_CLASSIC_ORGANIZERS:
			return uniq( [ ...state, action.payload.organizer ] );
		case types.REMOVE_CLASSIC_ORGANIZERS:
			return state.filter( ( organizer ) => organizer !== action.payload.id );
		case types.SET_CLASSIC_ORGANIZERS:
			return [ ...action.payload.organizers ];
		default:
			return state;
	}
};
