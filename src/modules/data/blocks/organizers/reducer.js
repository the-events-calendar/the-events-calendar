/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import {
	classic,
	classicSetInitialState,
	blocks,
} from './reducers';

export const setInitialState = ( entityRecord ) => {
	classicSetInitialState( entityRecord );
};

export default combineReducers( {
	blocks,
	classic,
} );
