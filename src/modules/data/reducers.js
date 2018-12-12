/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import blocks from './blocks';
import ui from './ui';
import search from './search';
import details from './details';

export default combineReducers( {
	blocks,
	ui,
	search,
	details,
} );

