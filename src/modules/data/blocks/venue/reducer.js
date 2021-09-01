/**
 * Internal dependencies
 */
import * as types from './types';
import { editorDefaults, mapsAPI } from '@moderntribe/common/utils/globals';

export const DEFAULT_STATE = {
	venue: editorDefaults().venue ? editorDefaults().venue : 0,
	showMap: mapsAPI().embed,
	showMapLink: mapsAPI().embed,
};

export const defaultStateToMetaMap = {
	venue: '_EventVenueID',
	showMap: '_EventShowMap',
	showMapLink: '_EventShowMapLink',
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
		case types.SET_VENUE:
			return {
				...state,
				venue: action.payload.venue,
			};
		case types.SET_VENUE_MAP:
			return {
				...state,
				showMap: action.payload.showMap,
			};
		case types.SET_VENUE_MAP_LINK:
			return {
				...state,
				showMapLink: action.payload.showMapLink,
			};
		default:
			return state;
	}
};
