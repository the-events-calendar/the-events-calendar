/**
 * Internal dependencies
 */
import * as types from './types';

export const setVenue = ( id ) => ( {
	type: types.SET_VENUE,
	payload: {
		venue: id,
	},
} );

export const removeVenue = () => ( {
	type: types.SET_VENUE,
	payload: {
		venue: 0,
	},
} );

export const setShowMap = ( showMap ) => ( {
	type: types.SET_VENUE_MAP,
	payload: {
		showMap,
	},
} );

export const setShowMapLink = ( showMapLink ) => ( {
	type: types.SET_VENUE_MAP_LINK,
	payload: {
		showMapLink,
	},
} );

export const setInitialState = ( props ) => ( {
	type: types.SET_INITIAL_STATE,
	payload: props,
} );

