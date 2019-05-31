/**
 * Internal dependencies
 */
import * as types from './types';

export const enableDetailsIsLoading = ( id ) => ( {
	type: types.SET_DETAILS_IS_LOADING,
	payload: {
		id,
		isLoading: true,
	},
} );

export const disableDetailsIsLoading = ( id ) => ( {
	type: types.SET_DETAILS_IS_LOADING,
	payload: {
		id,
		isLoading: false,
	},
} );

export const setDetails = ( id, details ) => ( {
	type: types.SET_DETAILS,
	payload: {
		id,
		details,
	},
} );

export const setDetailsPostType = ( id, postType ) => ( {
	type: types.SET_DETAILS_POST_TYPE,
	payload: {
		id,
		postType,
	},
} );
