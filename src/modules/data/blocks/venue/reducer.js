/**
 * Internal dependencies
 */
import * as types from './types';
import {
	classic,
	classicSetInitialState,
	blocks,
} from './reducers';
import {combineReducers} from "redux";

export const setInitialState = ( data ) => {
	classicSetInitialState( data );
};

export default combineReducers( {
	blocks,
	classic,
} );