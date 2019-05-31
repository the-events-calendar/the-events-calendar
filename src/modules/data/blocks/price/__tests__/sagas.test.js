/**
 * External dependencies
 */
import { takeEvery, put, call, all } from 'redux-saga/effects';

/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

/**
 * Internal Dependencies
 */
import * as types from '../types';
import { DEFAULT_STATE } from '../reducer';
import * as actions from '../actions';
import { priceSettings } from '@moderntribe/common/utils/globals';
import watchers, * as sagas from '../sagas';

jest.mock( '@wordpress/data', () => {
	const isEditedPostNew = () => {};
	return {
		select: () => ( {
			isEditedPostNew,
		} ),
	};
} );

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
		let action, settings;
		beforeEach( () => {
			action = { payload: {
				get: jest.fn(
					( name, _default ) => DEFAULT_STATE[ name ] || _default
				),
			} };
			settings = {
				default_currency: 'USD',
				defaultCurrencyPosition: 'prefix',
			};
		} );

		it( 'should handle new events', () => {
			const gen = sagas.setInitialState( action );
			expect( gen.next().value ).toEqual(
				call( priceSettings )
			);
			expect( gen.next( settings ).value ).toEqual(
				call( [ select( 'core/editor' ), 'isEditedPostNew' ] )
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
			const gen = sagas.setInitialState( action );
			expect( gen.next().value ).toEqual(
				call( priceSettings )
			);
			expect( gen.next( settings ).value ).toEqual(
				call( [ select( 'core/editor' ), 'isEditedPostNew' ] )
			);
			expect( gen.next( false ).value ).toEqual(
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
			expect( gen.next().done ).toEqual( true );
		} );
	} );
} );
