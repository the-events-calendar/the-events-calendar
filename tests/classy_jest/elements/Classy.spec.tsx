import {
	afterEach,
	beforeAll,
	describe,
	expect,
	jest,
	test,
} from '@jest/globals';
import { Classy } from '../../../src/resources/packages/classy/elements';
import { render } from '@testing-library/react';
import { dispatch } from '@wordpress/data';
import {
	store,
	STORE_NAME,
} from '../../../src/resources/packages/classy/store';
import {
	registerMockStore,
	registerStoreIfNotRegistered,
	resetAllStores,
	unregisterStore,
} from '../__support__/store-mocks';

describe( 'Classy', () => {
	beforeAll( () => {
		registerStoreIfNotRegistered( STORE_NAME, store );
	} );

	afterEach( () => {
		jest.resetAllMocks();
		jest.restoreAllMocks();
		resetAllStores();
	} );

	describe( 'core/editor store available', () => {
		test( 'initial state render for new post', () => {
			registerMockStore( 'core/editor', {
				selectors: {
					getEditedPostAttribute: () => '',
					getEditedPostContent: () => '',
					getCurrentPostId: () => 2389,
				},
			} );

			const { container } = render( <Classy /> );

			expect( container ).toMatchSnapshot();
		} );

		test( 'initial state render with title for existing post', () => {
			registerMockStore( 'core/editor', {
				selectors: {
					getEditedPostAttribute( state, attribute: string ) {
						if ( attribute === 'title' ) {
							return 'Some Event';
						}

						if ( attribute === 'meta' ) {
							return {
								METADATA_EVENT_URL: 'https://example-event.com',
							};
						}

						return '';
					},
					getEditedPostContent: (): string => 'Lorem dolor',
					getCurrentPostId: () => 2389,
				},
			} );

			const { container } = render( <Classy /> );

			expect( container ).toMatchSnapshot();
		} );
	} );

	/* Skipping this while the alternate registry approach is worked out.
	describe( 'core/editor store not available', () => {
		beforeAll( () => {
			unregisterStore( 'core/editor' );
		} );

		test( 'initial state render for new post', () => {
			// @ts-ignore
			dispatch( STORE_NAME ).editPost( {
				title: '',
				content: '',
				currentPostId: 2389,
				meta: {},
			} );

			const { container } = render( <Classy /> );

			expect( container ).toMatchSnapshot();
		} );

		test( 'initial state render with title for existing post', () => {
			// @ts-ignore
			dispatch( STORE_NAME ).editPost( {
				title: 'Classy title',
				content: 'Classy content',
				currentPostId: 2389,
				meta: {
					METADATA_EVENT_URL: 'https://example-event.com',
				},
			} );

			const { container } = render( <Classy /> );

			expect( container ).toMatchSnapshot();
		} );
	} );
	*/
} );
