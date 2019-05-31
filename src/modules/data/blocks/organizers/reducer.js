/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import { classic, blocks } from './reducers';

export default combineReducers( {
	blocks,
	classic,
} );
