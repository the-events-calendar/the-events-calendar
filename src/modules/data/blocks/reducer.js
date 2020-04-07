/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import datetime from './datetime';
import organizers from './organizers';
import price, { setInitialState as priceSetInitialState } from './price';
import website from './website';
import venue, { setInitialState as venueSetInitialState } from './venue';
import classic from './classic';

export const setInitialState = ( data ) => {
	priceSetInitialState( data );
	venueSetInitialState( data );
};

export default combineReducers( {
	datetime,
	classic,
	venue,
	organizers,
	price,
	website,
} );
