/**
 * Internal dependencies
 */
import * as types from './types';

export const setWebsite = ( url ) => ( {
	type: types.SET_WEBSITE_URL,
	payload: {
		url,
	},
} );
