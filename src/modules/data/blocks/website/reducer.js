/**
 * Internal dependencies
 */
import * as types from './types';

export const setInitialState = ( data ) => {
	const { meta } = data;
	if ( meta.hasOwnProperty( '_EventURL' ) ) {
		DEFAULT_STATE.url = meta._EventURL;
	}
};

export const DEFAULT_STATE = {
	url: '',
};

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.SET_WEBSITE_URL:
			return {
				...state,
				url: action.payload.url,
			};
		default:
			return state;
	}
};
