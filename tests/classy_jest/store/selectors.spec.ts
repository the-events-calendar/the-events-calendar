import { getEditedPostAttribute } from '../../../src/resources/packages/classy/store/selectors';
import { afterEach, describe, expect, it, jest } from '@jest/globals';
import { select } from '@wordpress/data';

jest.mock( '@wordpress/data', () => ( {
	select: jest.fn(),
} ) );

describe( 'selectors', () => {
	afterEach( () => {
		jest.resetAllMocks();
		jest.resetModules();
	} );

	describe( 'getEditedPostAttribute', () => {
		it( 'should read attribute from core/editor store if available', () => {
			const mockCoreEditor = {
				getEditedPostAttribute: jest.fn( () => 'from core/editor' ),
			};

			( select as jest.Mock ).mockImplementation( ( store ) => {
				if ( store === 'core/editor' ) return mockCoreEditor;
				return {};
			} );
			const state = { title: 'from classy' };

			const title = getEditedPostAttribute( state, 'title' );

			expect( title ).toBe( 'from core/editor' );
			expect(
				mockCoreEditor.getEditedPostAttribute
			).toHaveBeenCalledTimes( 1 );
			expect(
				mockCoreEditor.getEditedPostAttribute
			).toHaveBeenCalledWith( 'title' );
		} );

		it( 'should read attribute from classy store if core/editor store not available', () => {
			( select as jest.Mock ).mockImplementation( ( store ) => {
				if ( store === 'core/editor' ) return null;
				return {};
			} );
			const state = { title: 'from classy' };

			const title = getEditedPostAttribute( state, 'title' );

			expect( title ).toBe( 'from classy' );
		} );
	} );
} );
