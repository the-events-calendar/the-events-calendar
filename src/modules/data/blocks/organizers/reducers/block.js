/**
 * Internal dependencies
 */
import * as types from './../types';

export const DEFAULT_STATE = {
	organizer: null,
};

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.ADD_ORGANIZER_BLOCK:
			return {
				...state,
				organizer: action.payload.organizer,
			};
		case types.REMOVE_ORGANIZER_BLOCK:
			return {
				...state,
				...DEFAULT_STATE,
			};
		default:
			return state;
	}
};
