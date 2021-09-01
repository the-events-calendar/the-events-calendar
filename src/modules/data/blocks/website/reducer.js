/**
 * Internal dependencies
 */
import * as types from './types';

export const DEFAULT_STATE = {
	url: '',
};

export const defaultStateToMetaMap = {
	url: '_EventURL',
};

export const setInitialState = ( data ) => {
	const { meta } = data;

	Object.keys( defaultStateToMetaMap ).forEach( ( key ) => {
		const metaKey = defaultStateToMetaMap[ key ];
		if ( meta.hasOwnProperty( metaKey ) ) {
			DEFAULT_STATE[ key ] = meta[ metaKey ];
		}
	} );
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
