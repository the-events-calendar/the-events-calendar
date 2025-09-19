// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import { describe, expect, it } from '@jest/globals';
import { actions } from '@tec/events/classy/store/actions';
import { SET_IS_USING_TICKETS, SET_TICKETS_SUPPORTED } from '@tec/events/classy/types/Actions';

describe( 'Store Actions', () => {
	describe( 'setIsUsingTickets', () => {
		it( 'creates SET_IS_USING_TICKETS action with correct payload', () => {
			const isUsing = true;
			const action = actions.setIsUsingTickets( isUsing );

			expect( action ).toEqual( {
				type: SET_IS_USING_TICKETS,
				isUsing,
			} );
		} );

		it( 'creates action with false value', () => {
			const isUsing = false;
			const action = actions.setIsUsingTickets( isUsing );

			expect( action ).toEqual( {
				type: SET_IS_USING_TICKETS,
				isUsing: false,
			} );
		} );

		it( 'handles boolean true value', () => {
			const action = actions.setIsUsingTickets( true );

			expect( action.type ).toBe( SET_IS_USING_TICKETS );
			expect( action.isUsing ).toBe( true );
			expect( typeof action.isUsing ).toBe( 'boolean' );
		} );

		it( 'handles boolean false value', () => {
			const action = actions.setIsUsingTickets( false );

			expect( action.type ).toBe( SET_IS_USING_TICKETS );
			expect( action.isUsing ).toBe( false );
			expect( typeof action.isUsing ).toBe( 'boolean' );
		} );
	} );

	describe( 'setTicketsSupported', () => {
		it( 'creates SET_TICKETS_SUPPORTED action with correct payload', () => {
			const supported = true;
			const action = actions.setTicketsSupported( supported );

			expect( action ).toEqual( {
				type: SET_TICKETS_SUPPORTED,
				supported,
			} );
		} );

		it( 'creates action with false value', () => {
			const supported = false;
			const action = actions.setTicketsSupported( supported );

			expect( action ).toEqual( {
				type: SET_TICKETS_SUPPORTED,
				supported: false,
			} );
		} );

		it( 'handles boolean true value', () => {
			const action = actions.setTicketsSupported( true );

			expect( action.type ).toBe( SET_TICKETS_SUPPORTED );
			expect( action.supported ).toBe( true );
			expect( typeof action.supported ).toBe( 'boolean' );
		} );

		it( 'handles boolean false value', () => {
			const action = actions.setTicketsSupported( false );

			expect( action.type ).toBe( SET_TICKETS_SUPPORTED );
			expect( action.supported ).toBe( false );
			expect( typeof action.supported ).toBe( 'boolean' );
		} );
	} );

	describe( 'Action Type Constants', () => {
		it( 'uses correct action type for setIsUsingTickets', () => {
			const action = actions.setIsUsingTickets( true );
			expect( action.type ).toBe( 'SET_IS_USING_TICKETS' );
		} );

		it( 'uses correct action type for setTicketsSupported', () => {
			const action = actions.setTicketsSupported( true );
			expect( action.type ).toBe( 'SET_TICKETS_SUPPORTED' );
		} );
	} );

	describe( 'Action Object Structure', () => {
		it( 'setIsUsingTickets action has required properties', () => {
			const action = actions.setIsUsingTickets( true );

			expect( action ).toHaveProperty( 'type' );
			expect( action ).toHaveProperty( 'isUsing' );
			expect( Object.keys( action ) ).toHaveLength( 2 );
		} );

		it( 'setTicketsSupported action has required properties', () => {
			const action = actions.setTicketsSupported( true );

			expect( action ).toHaveProperty( 'type' );
			expect( action ).toHaveProperty( 'supported' );
			expect( Object.keys( action ) ).toHaveLength( 2 );
		} );
	} );
} );
