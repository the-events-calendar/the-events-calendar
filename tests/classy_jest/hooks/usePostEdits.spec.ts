import { usePostEdits } from '../../../src/resources/packages/classy/hooks';
import {
	beforeEach,
	afterEach,
	beforeAll,
	describe,
	expect,
	test,
	jest,
} from '@jest/globals';
import {
	registerMockStore,
	registerStoreIfNotRegistered,
	resetAllStores,
	unregisterStore,
} from '../__support__/store-mocks';
import { renderHook, act } from '@testing-library/react';
import {
	STORE_NAME,
	store,
} from '../../../src/resources/packages/classy/store';
import { METADATA_EVENT_URL } from '../../../src/resources/packages/classy/constants';

describe( 'usePostEdits', () => {
	beforeEach( () => {
		registerStoreIfNotRegistered( STORE_NAME, store );
	} );

	afterEach( () => {
		jest.clearAllMocks();
		jest.resetModules();
		resetAllStores();
	} );

	describe( 'core/editor store available', () => {
		test( 'initial empty state', () => {
			registerMockStore( 'core/editor', {
				selectors: {
					getEditedPostAttribute: (): string => '',
					getEditedPostContent: (): string => '',
				},
			} );

			const {
				result: {
					current: { postTitle, postContent, meta, editPost },
				},
			} = renderHook( () => usePostEdits() );

			expect( postTitle ).toEqual( '' );
			expect( postContent ).toEqual( '' );
			expect( meta ).toEqual( {} );
			expect( typeof editPost ).toBe( 'function' );
		} );

		test( 'existing state', () => {
			registerMockStore( 'core/editor', {
				selectors: {
					getEditedPostAttribute: (
						_state,
						attribute: string
					): any => {
						if ( attribute === 'title' ) {
							return 'Some Event';
						}

						if ( attribute === 'meta' ) {
							return {
								[ METADATA_EVENT_URL ]:
									'https://example-event.com',
							};
						}

						throw new Error(
							`Unexpected attribute fetch for ${ attribute }`
						);
					},
					getEditedPostContent: (): string => 'Lorem dolor',
				},
			} );

			const {
				result: {
					current: { postTitle, postContent, meta, editPost },
				},
			} = renderHook( () => usePostEdits() );

			expect( postTitle ).toEqual( 'Some Event' );
			expect( postContent ).toEqual( 'Lorem dolor' );
			expect( meta ).toEqual( {
				[ METADATA_EVENT_URL ]: 'https://example-event.com',
			} );
			expect( typeof editPost ).toBe( 'function' );
		} );
	} );

	describe( 'core/editor store not available', () => {
		beforeAll( () => {
			unregisterStore( 'core/editor' );
		} );

		test( 'initial empty state', () => {
			const {
				result: {
					current: { postTitle, postContent, meta, editPost },
				},
			} = renderHook( () => usePostEdits() );

			expect( postTitle ).toEqual( '' );
			expect( postContent ).toEqual( '' );
			expect( meta ).toEqual( {} );
			expect( typeof editPost ).toBe( 'function' );
		} );

		test( 'existing state', async () => {
			// Render the hook a first time with initial (empty) state.
			const { result, rerender } = renderHook( () => usePostEdits() );

			// Update the state dispatching an action on the hook.
			await act( () =>
				result.current.editPost( {
					title: 'Some Classy Event',
					content: 'Classy dolor',
					meta: {
						[ METADATA_EVENT_URL ]: 'https://classy-event.com',
					},
				} )
			);

			// Re-render the hook to get the updated values.
			rerender();

			expect( result.current.postTitle ).toEqual( 'Some Classy Event' );
			expect( result.current.postContent ).toEqual( 'Classy dolor' );
			expect( result.current.meta ).toEqual( {
				[ METADATA_EVENT_URL ]: 'https://classy-event.com',
			} );
		} );
	} );
} );
