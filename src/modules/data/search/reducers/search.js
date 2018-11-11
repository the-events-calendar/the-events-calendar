/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/search';
import { editor } from '@moderntribe/common/data';

export const DEFAULT_STATE = {
	term: '',
	results: [],
	page: 1,
	totalPages: 0,
	isLoading: false,
	postType: editor.EVENT,
};

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.ADD_BLOCK:
			return DEFAULT_STATE;
		case types.CLEAR_BLOCK:
			return {
				...DEFAULT_STATE,
				postType: state.postType,
			};
		case types.SET_TERM:
			return {
				...state,
				term: action.payload.term,
			};
		case types.SET_RESULTS:
			return {
				...state,
				results: action.payload.results,
			};
		case types.ADD_RESULTS:
			return {
				...state,
				results: [ ...state.results, ...action.payload.results ],
			};
		case types.SET_PAGE:
			return {
				...state,
				page: action.payload.page,
			};
		case types.SET_TOTAL_PAGES:
			return {
				...state,
				totalPages: action.payload.totalPages,
			};
		case types.SET_SEARCH_IS_LOADING: {
			return {
				...state,
				isLoading: action.payload.isLoading,
			};
		}
		case types.SET_SEARCH_POST_TYPE: {
			return {
				...state,
				postType: action.payload.postType,
			};
		}
		default:
			return state;
	}
};
