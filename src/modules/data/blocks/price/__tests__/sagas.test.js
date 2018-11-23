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
import { isTruthy } from '@moderntribe/common/utils/string';
import { priceSettings } from '@moderntribe/common/utils/globals';
import watchers, * as sagas from '../sagas';

describe( 'Price Block sagas', () => {
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
		let action, settings, clone;
		beforeEach( () => {
			action = { payload: {
				get: jest.fn(
					( name, _default ) => DEFAULT_STATE[ name ] || _default
				),
			} };
			settings = {
				isNewEvent: 'true',
				default_currency: 'USD',
				defaultCurrencyPosition: 'prefix',
			};
		} );

		it( 'should handle new events', () => {
			const gen = cloneableGenerator( sagas.setInitialState )( action );
			expect( gen.next().value ).toEqual(
				call( priceSettings )
			);
			clone = gen.clone();
			expect( gen.next( settings ).value ).toEqual(
				call( isTruthy, settings.isNewEvent )
			);
			expect( gen.next( true ).value ).toEqual(
				all( [
					put( actions.setPosition( settings.defaultCurrencyPosition ) ),
					put( actions.setSymbol( settings.defaultCurrencySymbol ) ),
					put( actions.setCost( action.payload.get( 'cost', DEFAULT_STATE.cost ) ) ),
					put( actions.setDescription(
						action.payload.get( 'costDescription', DEFAULT_STATE.description ) )
					),
				] )
			);
			expect( gen.next().done ).toEqual( true );
		} );
		it( 'should handle existing events', () => {
			settings.isNewEvent = 'false';
			expect( clone.next( settings ).value ).toEqual(
				call( isTruthy, settings.isNewEvent )
			);
			expect( clone.next( false ).value ).toEqual(
				all( [
					put( actions.setPosition(
						action.payload.get( 'currencyPosition', DEFAULT_STATE.position )
					) ),
					put( actions.setSymbol(
						action.payload.get( 'currencySymbol', DEFAULT_STATE.symbol )
					) ),
					put( actions.setCost( action.payload.get( 'cost', DEFAULT_STATE.cost ) ) ),
					put( actions.setDescription(
						action.payload.get( 'costDescription', DEFAULT_STATE.description ) )
					),
				] )
			);
			expect( clone.next().done ).toEqual( true );
		} );
	} );
} );
