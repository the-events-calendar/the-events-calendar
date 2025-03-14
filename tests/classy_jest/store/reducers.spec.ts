import { editPost } from '../../../src/resources/packages/classy/store/reducers';
import { editPost as editPostAction } from '../../../src/resources/packages/classy/store/actions';
import { afterEach, describe, expect, it, jest } from '@jest/globals';

describe( 'reducer', () => {
	afterEach( () => {
		jest.clearAllMocks();
	} );

	describe( 'editPost', () => {
		it( 'should update the state', () => {
			const action = editPostAction( { title: 'new title' } );

			const state = editPost( {}, action );

			expect( state ).toEqual( {
				title: 'new title',
			} );
		} );
	} );
} );
