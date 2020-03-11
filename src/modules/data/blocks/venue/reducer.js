/**
 * Internal dependencies
 */
import * as types from './types';
import { globals } from '@moderntribe/common/utils';

export const setInitialState = ( entityRecord ) => {
	DEFAULT_STATE.venue = entityRecord.meta._EventVenueID;
	DEFAULT_STATE.showMap = globals.mapsAPI().embed && entityRecord.meta._EventShowMap;
	DEFAULT_STATE.showMapLink = entityRecord.meta._EventShowMapLink;
};

export const DEFAULT_STATE = {
	venue: globals.editorDefaults().venue ? globals.editorDefaults().venue : 0,
	showMap: globals.mapsAPI().embed,
	showMapLink: globals.mapsAPI().embed,
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
