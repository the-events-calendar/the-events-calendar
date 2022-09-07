/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import datetime, { reducer as datetimeReducer } from './datetime';
import organizers, { reducer as organizersReducer } from './organizers';
import price, { reducer as priceReducer } from './price';
import website, { reducer as websiteReducer } from './website';
import venue, { reducer as venueReducer } from './venue';

export const setInitialState = ( data ) => {
	datetimeReducer.setInitialState( data );
	organizersReducer.setInitialState( data );
	priceReducer.setInitialState( data );
	websiteReducer.setInitialState( data );
	venueReducer.setInitialState( data );
};

export default combineReducers( {
	datetime,
	venue,
	organizers,
	price,
	website,
} );
