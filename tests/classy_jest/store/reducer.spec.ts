// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import { describe, expect, it, beforeEach } from '@jest/globals';
import { reducer } from '@tec/events/classy/store/reducer';
import { SET_IS_USING_TICKETS, SET_TICKETS_SUPPORTED } from '@tec/events/classy/types/Actions';
import { StoreState } from '@tec/events/classy/types/Store';

describe( 'Store Reducer', () => {
	const initialState: StoreState = {
		areTicketsSupported: false,
		isUsingTickets: false,
	};

	beforeEach( () => {
		jest.clearAllMocks();
	} );

	describe( 'Default State', () => {
		it( 'returns initial state when state is undefined', () => {
			const result = reducer( undefined, { type: 'UNKNOWN_ACTION' } as any );

			expect( result ).toEqual( initialState );
		} );

		it( 'returns initial state with correct structure', () => {
			const result = reducer( undefined, { type: 'UNKNOWN_ACTION' } as any );

			expect( result ).toBeDefined();
			expect( result.areTicketsSupported ).toBe( false );
			expect( result.isUsingTickets ).toBe( false );
		} );

		it( 'returns current state when no action is provided', () => {
			const currentState: StoreState = {
				areTicketsSupported: true,
				isUsingTickets: true,
			};
			const result = reducer( currentState, { type: 'UNKNOWN_ACTION' } as any );

			expect( result ).toEqual( currentState );
		} );
	} );

	describe( 'SET_IS_USING_TICKETS', () => {
		it( 'sets isUsingTickets to true', () => {
			const action = {
				type: SET_IS_USING_TICKETS,
				isUsing: true,
			};
			const result = reducer( initialState, action );

			expect( result.isUsingTickets ).toBe( true );
			expect( result.areTicketsSupported ).toBe( false );
		} );

		it( 'sets isUsingTickets to false', () => {
			const action = {
				type: SET_IS_USING_TICKETS,
				isUsing: false,
			};
			const result = reducer( initialState, action );

			expect( result.isUsingTickets ).toBe( false );
			expect( result.areTicketsSupported ).toBe( false );
		} );

		it( 'preserves other state properties when setting isUsingTickets', () => {
			const currentState: StoreState = {
				areTicketsSupported: true,
				isUsingTickets: false,
			};
			const action = {
				type: SET_IS_USING_TICKETS,
				isUsing: true,
			};
			const result = reducer( currentState, action );

			expect( result.isUsingTickets ).toBe( true );
			expect( result.areTicketsSupported ).toBe( true );
		} );

		it( 'handles boolean true value correctly', () => {
			const action = {
				type: SET_IS_USING_TICKETS,
				isUsing: true,
			};
			const result = reducer( initialState, action );

			expect( result.isUsingTickets ).toBe( true );
			expect( typeof result.isUsingTickets ).toBe( 'boolean' );
		} );

		it( 'handles boolean false value correctly', () => {
			const action = {
				type: SET_IS_USING_TICKETS,
				isUsing: false,
			};
			const result = reducer( initialState, action );

			expect( result.isUsingTickets ).toBe( false );
			expect( typeof result.isUsingTickets ).toBe( 'boolean' );
		} );
	} );

	describe( 'SET_TICKETS_SUPPORTED', () => {
		it( 'sets areTicketsSupported to true', () => {
			const action = {
				type: SET_TICKETS_SUPPORTED,
				supported: true,
			};
			const result = reducer( initialState, action );

			expect( result.areTicketsSupported ).toBe( true );
			expect( result.isUsingTickets ).toBe( false );
		} );

		it( 'sets areTicketsSupported to false', () => {
			const action = {
				type: SET_TICKETS_SUPPORTED,
				supported: false,
			};
			const result = reducer( initialState, action );

			expect( result.areTicketsSupported ).toBe( false );
			expect( result.isUsingTickets ).toBe( false );
		} );

		it( 'preserves other state properties when setting areTicketsSupported', () => {
			const currentState: StoreState = {
				areTicketsSupported: false,
				isUsingTickets: true,
			};
			const action = {
				type: SET_TICKETS_SUPPORTED,
				supported: true,
			};
			const result = reducer( currentState, action );

			expect( result.areTicketsSupported ).toBe( true );
			expect( result.isUsingTickets ).toBe( true );
		} );

		it( 'handles boolean true value correctly', () => {
			const action = {
				type: SET_TICKETS_SUPPORTED,
				supported: true,
			};
			const result = reducer( initialState, action );

			expect( result.areTicketsSupported ).toBe( true );
			expect( typeof result.areTicketsSupported ).toBe( 'boolean' );
		} );

		it( 'handles boolean false value correctly', () => {
			const action = {
				type: SET_TICKETS_SUPPORTED,
				supported: false,
			};
			const result = reducer( initialState, action );

			expect( result.areTicketsSupported ).toBe( false );
			expect( typeof result.areTicketsSupported ).toBe( 'boolean' );
		} );
	} );

	describe( 'Unknown Actions', () => {
		it( 'returns current state for unknown action types', () => {
			const currentState: StoreState = {
				areTicketsSupported: true,
				isUsingTickets: true,
			};
			const action = {
				type: 'UNKNOWN_ACTION_TYPE',
				payload: 'some data',
			} as any;
			const result = reducer( currentState, action );

			expect( result ).toEqual( currentState );
			expect( result ).toBe( currentState ); // Should return the same reference
		} );

		it( 'returns initial state for unknown action when state is undefined', () => {
			const action = {
				type: 'UNKNOWN_ACTION_TYPE',
				payload: 'some data',
			} as any;
			const result = reducer( undefined, action );

			expect( result ).toEqual( initialState );
		} );
	} );

	describe( 'State Immutability', () => {
		it( 'does not mutate the original state', () => {
			const currentState: StoreState = {
				areTicketsSupported: false,
				isUsingTickets: false,
			};
			const originalState = JSON.parse( JSON.stringify( currentState ) );
			const action = {
				type: SET_IS_USING_TICKETS,
				isUsing: true,
			};

			reducer( currentState, action );

			expect( currentState ).toEqual( originalState );
		} );

		it( 'creates new state object on changes', () => {
			const currentState: StoreState = {
				areTicketsSupported: false,
				isUsingTickets: false,
			};
			const action = {
				type: SET_IS_USING_TICKETS,
				isUsing: true,
			};
			const result = reducer( currentState, action );

			expect( result ).not.toBe( currentState );
			expect( result.isUsingTickets ).not.toBe( currentState.isUsingTickets );
		} );

		it( 'preserves state reference when no changes occur', () => {
			const currentState: StoreState = {
				areTicketsSupported: false,
				isUsingTickets: false,
			};
			const action = {
				type: 'UNKNOWN_ACTION',
				payload: 'data',
			} as any;
			const result = reducer( currentState, action );

			expect( result ).toBe( currentState );
		} );
	} );

	describe( 'Multiple State Updates', () => {
		it( 'handles multiple state updates correctly', () => {
			let state = initialState;

			// First update: set tickets supported
			state = reducer( state, {
				type: SET_TICKETS_SUPPORTED,
				supported: true,
			} );

			expect( state.areTicketsSupported ).toBe( true );
			expect( state.isUsingTickets ).toBe( false );

			// Second update: set using tickets
			state = reducer( state, {
				type: SET_IS_USING_TICKETS,
				isUsing: true,
			} );

			expect( state.areTicketsSupported ).toBe( true );
			expect( state.isUsingTickets ).toBe( true );

			// Third update: set not using tickets
			state = reducer( state, {
				type: SET_IS_USING_TICKETS,
				isUsing: false,
			} );

			expect( state.areTicketsSupported ).toBe( true );
			expect( state.isUsingTickets ).toBe( false );
		} );
	} );
} );
