/**
 * Internal dependencies
 */
import * as types from './types';
import { globals } from '@moderntribe/common/utils';

export const setInitialState = () => {
	if ( globals.wpCoreEditor.isCleanNewPost() ) {
		return;
	}

	const postId = globals.wpCoreEditor.getCurrentPostId();
	const entityRecord = globals.wpCore.getEntityRecord( 'postType', 'tribe_events', postId );

	if ( entityRecord.meta._EventVenueID ) {
		DEFAULT_STATE.venue = entityRecord.meta._EventVenueID;
	}
};

export const DEFAULT_STATE = {
	venue: globals.editorDefaults().venue ? globals.editorDefaults().venue : 0,
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
