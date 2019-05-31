/**
 * Internal dependencies
 */
import * as types from './types';
import { search } from './reducers';

export default ( state = {}, action ) => {
	switch ( action.type ) {
		case types.ADD_BLOCK:
		case types.CLEAR_BLOCK:
		case types.SET_TERM:
		case types.SET_RESULTS:
		case types.ADD_RESULTS:
		case types.SET_PAGE:
		case types.SET_TOTAL_PAGES:
		case types.SET_SEARCH_IS_LOADING:
		case types.SET_SEARCH_POST_TYPE:
			return {
				...state,
				[ action.payload.id ]: search( state[ action.payload.id ], action ),
			};
		default:
			return state;
	}
};
