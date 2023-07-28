/**
 * External dependencies
 */
import { uniq } from 'lodash';

/**
 * Internal dependencies
 */
import * as types from './../types';

export const DEFAULT_STATE = [];

export const setInitialState = ( data ) => {
	const { meta } = data;
	if ( meta.hasOwnProperty( '_EventVenueID' ) ) {
		DEFAULT_STATE.push( ...meta._EventVenueID );
	}
};

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.ADD_CLASSIC_VENUES:
			return uniq( [ ...state, action.payload.venue ] );
		case types.SET_VENUE:
			return [ action.payload.venue ];
		case types.REMOVE_CLASSIC_VENUES:
			return state.filter( ( venue ) => venue !== action.payload.venue );
		default:
			return state;
	}
};
