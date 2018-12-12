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

export const setLabel = ( label ) => ( {
	type: types.SET_WEBSITE_LABEL,
	payload: {
		label,
	},
} );

export const setInitialState = ( props ) => ( {
	type: types.SET_INITIAL_STATE,
	payload: props,
} );
