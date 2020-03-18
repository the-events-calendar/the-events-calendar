/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import datetime from './datetime';
import organizers, { setInitialState as organizersSetInitialState } from './organizers';
import price from './price';
import website from './website';
import venue from './venue';
import sharing from './sharing';

export const setInitialState = ( entityRecord ) => {
	organizersSetInitialState( entityRecord );
};

export default combineReducers( {
	datetime,
	venue,
	organizers,
	price,
	website,
	sharing,
} );
