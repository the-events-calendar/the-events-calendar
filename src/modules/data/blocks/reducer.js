/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import datetime, { setInitialState as datetimeSetInitialState } from './datetime';
import organizers, { setInitialState as organizersSetInitialState } from './organizers';
import price, { setInitialState as priceSetInitialState } from './price';
import website, { setInitialState as websiteSetInitialState } from './website';
import venue, { setInitialState as venueSetInitialState } from './venue';

export const setInitialState = ( entityRecord ) => {
	datetimeSetInitialState( entityRecord );
	organizersSetInitialState( entityRecord );
	priceSetInitialState( entityRecord );
	venueSetInitialState( entityRecord );
	websiteSetInitialState( entityRecord );
};

export default combineReducers( {
	datetime,
	venue,
	organizers,
	price,
	website,
} );
