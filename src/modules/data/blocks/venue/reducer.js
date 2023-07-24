/**
 * Internal dependencies
 */
import * as types from './types';
import {
	classic,
	classicSetInitialState,
	blocks,
} from './reducers';
import { editorDefaults, mapsAPI } from '@moderntribe/common/utils/globals';
import {combineReducers} from "redux";

export const setInitialState = ( data ) => {
	classicSetInitialState( data );
};

export default combineReducers( {
	blocks,
	classic,
} );

/*
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

 */
