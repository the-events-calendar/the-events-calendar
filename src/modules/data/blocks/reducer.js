/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import datetime from './datetime';
import organizers from './organizers';
import price from './price';
import website from './website';
import venue from './venue';
import classic from './classic';

export default combineReducers( {
	datetime,
	classic,
	venue,
	organizers,
	price,
	website,
} );
