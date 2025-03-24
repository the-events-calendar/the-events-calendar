import { reducer } from '../../../src/resources/packages/classy/store/reducer';
import { editPost as editPostAction } from '../../../src/resources/packages/classy/store/actions';
import { afterEach, describe, expect, it, jest } from '@jest/globals';

describe( 'reducer', () => {
	afterEach( () => {
		jest.clearAllMocks();
	} );

	describe( 'editPost', () => {
		it( 'should update the state', () => {
			const action = editPostAction( { title: 'new title' } );

			const state = reducer( {}, action );

			expect( state ).toEqual( {
				title: 'new title',
			} );
		} );
	} );
} );
