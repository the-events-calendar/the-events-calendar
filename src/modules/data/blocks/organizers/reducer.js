/**
 * External dependencies
 */
import { combineReducers } from 'redux';

/**
 * Internal dependencies
 */
import { classic, classicSetInitialState, blocks } from './reducers';

export const setInitialState = ( data ) => {
	classicSetInitialState( data );
};

export default combineReducers( {
	blocks,
	classic,
} );
