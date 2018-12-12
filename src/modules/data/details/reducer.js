/**
 * Internal dependencies
 */
import * as types from './types';
import { details } from './reducers';

export default ( state = {}, action ) => {
	switch ( action.type ) {
		case types.SET_DETAILS:
		case types.SET_DETAILS_IS_LOADING:
		case types.SET_DETAILS_POST_TYPE:
			return {
				...state,
				[ action.payload.id ]: details( state[ action.payload.id ], action ),
			};
		default:
			return state;
	}
};
