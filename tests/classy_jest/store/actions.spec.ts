import { editPost } from '../../../src/resources/packages/classy/store/actions';
import { afterEach, describe, expect, it, jest } from '@jest/globals';
import { dispatch } from '@wordpress/data';
import { ACTION_CLASSY_EDIT_POST } from '../../../src/resources/packages/classy/store/constants';

jest.mock( '@wordpress/data', () => ( {
	dispatch: jest.fn(),
} ) );

describe( 'editPost', () => {
	afterEach( () => {
		jest.clearAllMocks();
		jest.resetModules();
	} );

	it( 'should dispatch the updates to the core/editor store if available', () => {
		const mockCoreEditor = {
			editPost: jest.fn(),
		};
		( dispatch as jest.Mock ).mockImplementation( ( store ) => {
			if ( store === 'core/editor' ) return mockCoreEditor;
			return {};
		} );
		const updates = { title: 'new title' };

		const action = editPost( updates );

		expect( dispatch ).toHaveBeenCalledWith( 'core/editor' );
		expect( dispatch ).toHaveBeenCalledTimes( 1 );
		expect( mockCoreEditor.editPost ).toHaveBeenCalledWith( updates );
		expect( mockCoreEditor.editPost ).toHaveBeenCalledTimes( 1 );
		expect( action ).toEqual( {
			type: ACTION_CLASSY_EDIT_POST,
			updates,
		} );
	} );

	it( 'should not dispatch updates to the core/editor store if not available', () => {
		( dispatch as jest.Mock ).mockImplementation( ( store ) => {
			if ( store === 'core/editor' ) return null;
			return {};
		} );
		const updates = { title: 'new title' };

		const action = editPost( updates );

		expect( dispatch ).toHaveBeenCalledWith( 'core/editor' );
		expect( dispatch ).toHaveBeenCalledTimes( 1 );
		expect( action ).toEqual( {
			type: ACTION_CLASSY_EDIT_POST,
			updates,
		} );
	} );
} );
