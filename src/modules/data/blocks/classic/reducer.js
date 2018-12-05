/**
 * Internal dependencies
 */
import * as types from './types';

export const DEFAULT_STATE = {
	detailsTitle: '',
	organizerTitle: '',
};

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.SET_CLASSIC_DETAILS_TITLE:
			return {
				...state,
				detailsTitle: action.payload.title,
			};

		case types.SET_CLASSIC_ORGANIZERS_TITLE:
			return {
				...state,
				organizerTitle: action.payload.title,
			};

		default:
			return state;
	}
};
