import { usePostEdits } from '../../../src/resources/packages/classy/hooks';
import { afterEach, describe, expect, it, jest } from '@jest/globals';
import { useSelect, useDispatch } from '@wordpress/data';

jest.mock( '@wordpress/data', () => ( {
	useDispatch: jest.fn(),
	useSelect: jest.fn(),
} ) );

describe( 'usePostEdits', () => {
	afterEach( () => {
		jest.clearAllMocks();
		jest.resetModules();
	} );

	it( 'should select and dispatch from/to classy store', () => {
		const mockCoreEditor = {
			getEditedPostAttribute: jest.fn( () => 'some title' ),
		};
		const mockSelect = jest.fn( ( store: string ) => {
			if ( store === 'tec/classy' ) return mockCoreEditor;
			return null;
		} );
		( useSelect as jest.Mock ).mockImplementation(
			( mapSelect: Function, dependencies ) => {
				return mapSelect( mockSelect );
			}
		);
		const mockEditPost = jest.fn();
		( useDispatch as jest.Mock ).mockImplementation( ( store: string ) => {
			if ( store === 'tec/classy' ) return { editPost: mockEditPost };
			return null;
		} );

		const { postTitle, editPost } = usePostEdits();

		expect( postTitle ).toEqual( 'some title' );
		expect( editPost ).toBe( mockEditPost );
	} );
} );
