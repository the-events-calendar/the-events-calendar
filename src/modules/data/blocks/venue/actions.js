/**
 * Internal dependencies
 */
import * as types from './types';
import { DEFAULT_STATE } from './reducer';

export const setVenue = ( id ) => ( {
	type: types.SET_VENUE,
	payload: {
		venue: id,
	},
} );

export const removeVenue = () => ( {
	type: types.SET_VENUE,
	payload: {
		venue: undefined,
	},
} );

export const toggleVenueMap = () => ( { type: types.TOGGLE_VENUE_MAP } );

export const setShowMap = ( showMap ) => ( {
	type: types.SET_VENUE_MAP,
	payload: {
		showMap,
	},
} );

export const toggleVenueMapLink = () => ( { type: types.TOGGLE_VENUE_MAP_LINK } );

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

