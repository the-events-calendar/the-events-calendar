/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import datetime from './datetime';
import organizers, { setInitialState as organizersSetInitialState } from './organizers';
import price, { setInitialState as priceSetInitialState } from './price';
import website, { setInitialState as websiteSetInitialState } from './website';
import venue, { setInitialState as venueSetInitialState } from './venue';
import sharing from './sharing';

export const setInitialState = ( data ) => {
	organizersSetInitialState( entityRecord );
	priceSetInitialState( data );
	venueSetInitialState( data );
	websiteSetInitialState( data );
};

export default combineReducers( {
	datetime,
	venue,
	organizers,
	price,
	website,
	sharing,
} );
