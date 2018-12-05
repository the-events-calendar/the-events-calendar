/**
 * External dependencies
 */
import { takeEvery, put, call, all } from 'redux-saga/effects';
import { cloneableGenerator } from 'redux-saga/utils';

/**
 * Internal Dependencies
 */
import * as types from '../types';
import { DEFAULT_STATE } from '../reducer';
import * as actions from '../actions';
import watchers, * as sagas from '../sagas';

describe( 'Sharing Block sagas', () => {
	describe( 'watchers', () => {
		it( 'should watch actions', () => {
			const gen = watchers();
			expect( gen.next().value ).toEqual(
				takeEvery( types.SET_INITIAL_STATE, sagas.setInitialState )
			);
			expect( gen.next().done ).toEqual( true );
		} );
	} );
	describe( 'setInitialState', () => {
		let action;
		beforeEach( () => {
			action = { payload: {
				get: jest.fn(
					( name, _default ) => DEFAULT_STATE[ name ] || _default
				),
			} };
		} );

		it( 'should set initial state', () => {
			const gen = cloneableGenerator( sagas.setInitialState )( action );
			expect( gen.next().value ).toEqual(
				all( [
					put( actions.setDetailsTitle(
						action.payload.get( 'detailsTitle', DEFAULT_STATE.detailsTitle ) )
					),
					put( actions.setOrganizerTitle(
						action.payload.get( 'organizerTitle', DEFAULT_STATE.organizerTitle ) )
					),
				] )
			);
			expect( gen.next().done ).toEqual( true );
		} );
	} );
} );
