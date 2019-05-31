/**
 * Internal dependencies
 */
import * as types from './types';

export const setDetailsTitle = ( title ) => ( {
	type: types.SET_CLASSIC_DETAILS_TITLE,
	payload: {
		title,
	},
} );

export const setOrganizerTitle = ( title ) => ( {
	type: types.SET_CLASSIC_ORGANIZERS_TITLE,
	payload: {
		title,
	},
} );

export const setInitialState = ( props ) => ( {
	type: types.SET_INITIAL_STATE,
	payload: props,
} );
