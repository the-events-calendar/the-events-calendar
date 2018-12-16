/**
 * Internal dependencies
 */
import * as types from './types';
import { editorDefaults } from '@moderntribe/common/utils/globals';

export const DEFAULT_STATE = {
	venue: editorDefaults().venue ? editorDefaults().venue : 0,
	showMap: true,
	showMapLink: true,
};

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.SET_VENUE:
			return {
				...state,
				venue: action.payload.venue,
			};
		case types.TOGGLE_VENUE_MAP:
			return {
				...state,
				showMap: ! state.showMap,
			};
		case types.SET_VENUE_MAP:
			return {
				...state,
				showMap: action.payload.showMap,
			};
		case types.TOGGLE_VENUE_MAP_LINK:
			return {
				...state,
				showMapLink: ! state.showMapLink,
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
