// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import { describe, expect, it } from '@jest/globals';
import { storeConfig } from '@tec/events/classy/store';
import { StoreState } from '@tec/events/classy/types/Store';

describe( 'Store Configuration', () => {
	describe( 'storeConfig', () => {
		it( 'exports store configuration object', () => {
			expect( storeConfig ).toBeDefined();
			expect( typeof storeConfig ).toBe( 'object' );
		} );

		it( 'has required properties', () => {
			expect( storeConfig ).toHaveProperty( 'reducer' );
			expect( storeConfig ).toHaveProperty( 'actions' );
			expect( storeConfig ).toHaveProperty( 'selectors' );
			expect( storeConfig ).toHaveProperty( 'initialState' );
		} );

		it( 'has correct property types', () => {
			expect( typeof storeConfig.reducer ).toBe( 'function' );
			expect( typeof storeConfig.actions ).toBe( 'object' );
			expect( typeof storeConfig.selectors ).toBe( 'object' );
			expect( typeof storeConfig.initialState ).toBe( 'object' );
		} );

		it( 'has correct initial state', () => {
			const expectedInitialState: StoreState = {
				areTicketsSupported: false,
				isUsingTickets: false,
			};

			expect( storeConfig.initialState ).toEqual( expectedInitialState );
		} );

		it( 'initial state has correct structure', () => {
			expect( storeConfig.initialState ).toHaveProperty( 'areTicketsSupported' );
			expect( storeConfig.initialState ).toHaveProperty( 'isUsingTickets' );
			expect( storeConfig.initialState.areTicketsSupported ).toBe( false );
			expect( storeConfig.initialState.isUsingTickets ).toBe( false );
		} );

		it( 'actions object has required methods', () => {
			expect( storeConfig.actions ).toHaveProperty( 'setIsUsingTickets' );
			expect( storeConfig.actions ).toHaveProperty( 'setTicketsSupported' );
			expect( typeof storeConfig.actions.setIsUsingTickets ).toBe( 'function' );
			expect( typeof storeConfig.actions.setTicketsSupported ).toBe( 'function' );
		} );

		it( 'selectors object has required methods', () => {
			const expectedSelectors = [
				'getPostMeta',
				'getSettings',
				'getEventDateTimeDetails',
				'getEditedPostOrganizerIds',
				'getEditedPostVenueIds',
				'areTicketsSupported',
				'isUsingTickets',
				'isNewEvent',
				'getVenuesLimit',
			];

			expectedSelectors.forEach( ( selector ) => {
				expect( storeConfig.selectors ).toHaveProperty( selector );
				expect( typeof storeConfig.selectors[ selector ] ).toBe( 'function' );
			} );
		} );

		it( 'reducer function works correctly', () => {
			const initialState = storeConfig.initialState;
			const action = {
				type: 'SET_IS_USING_TICKETS',
				isUsing: true,
			};

			const result = storeConfig.reducer( initialState, action );

			expect( result ).toBeDefined();
			expect( result.isUsingTickets ).toBe( true );
			expect( result.areTicketsSupported ).toBe( false );
		} );

		it( 'actions work correctly', () => {
			const setIsUsingTicketsAction = storeConfig.actions.setIsUsingTickets( true );
			const setTicketsSupportedAction = storeConfig.actions.setTicketsSupported( true );

			expect( setIsUsingTicketsAction ).toEqual( {
				type: 'SET_IS_USING_TICKETS',
				isUsing: true,
			} );

			expect( setTicketsSupportedAction ).toEqual( {
				type: 'SET_TICKETS_SUPPORTED',
				supported: true,
			} );
		} );

		it( 'initial state is immutable', () => {
			const originalState = storeConfig.initialState;
			const modifiedState = { ...originalState, isUsingTickets: true };

			expect( originalState.isUsingTickets ).toBe( false );
			expect( modifiedState.isUsingTickets ).toBe( true );
		} );

		it( 'store configuration is properly structured for Redux', () => {
			// Test that the configuration can be used with Redux
			const state = storeConfig.initialState;
			const action = storeConfig.actions.setIsUsingTickets( true );
			const newState = storeConfig.reducer( state, action );

			expect( newState ).not.toBe( state ); // New state object
			expect( newState.isUsingTickets ).toBe( true );
		} );
	} );
} );
