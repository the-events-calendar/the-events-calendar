import {
	afterEach,
	beforeAll,
	beforeEach,
	describe,
	expect,
	jest,
	test,
} from '@jest/globals';
import { EventTitle } from '../../../../src/resources/packages/classy/elements';
import { render } from '@testing-library/react';
import { dispatch } from '@wordpress/data';
import {
	store,
	STORE_NAME,
} from '../../../../src/resources/packages/classy/store';
import {
	registerMockStore,
	registerStoreIfNotRegistered,
	resetAllStores,
	unregisterStore,
} from '../../__support__/store-mocks';
import { METADATA_EVENT_URL } from '../../../../src/resources/packages/classy/constants';

describe( 'EventTitle ', () => {
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
				},
			} );

			const { container } = render( <EventTitle title="Event Title" /> );

			expect( container ).toMatchSnapshot();
		} );

		test( 'initial state render with title for existing post', () => {
			registerMockStore( 'core/editor', {
				selectors: {
					getEditedPostAttribute( state, attribute: string ) {
						if ( attribute === 'title' ) {
							return 'Test Event';
						}

						if ( attribute === 'meta' ) {
							return {
								[ METADATA_EVENT_URL ]:
									'https://example-event.com',
							};
						}

						throw new Error(
							`Unexpected fetch of attribute ${ attribute }`
						);
					},
					getEditedPostContent: (): string => 'Lorem Event',
				},
			} );

			const { container } = render( <EventTitle title="Event Title" /> );

			expect( container ).toMatchSnapshot();
		} );
	} );

	describe( 'core/editor store not available', () => {
		beforeAll( () => {
			unregisterStore( 'core/editor' );
		} );

		test( 'initial state render for new post', () => {
			// @ts-ignore
			dispatch( STORE_NAME ).editPost( {
				title: '',
				content: '',
				meta: {},
			} );

			const { container } = render( <EventTitle title="Event Title" /> );

			expect( container ).toMatchSnapshot();
		} );

		test( 'initial state render with title for existing post', () => {
			// @ts-ignore
			dispatch( STORE_NAME ).editPost( {
				title: 'Classy title',
				content: 'Classy content',
				meta: {
					[ METADATA_EVENT_URL ]: 'https://classy-event.com',
				},
			} );

			const { container } = render( <EventTitle title="Event Title" /> );

			expect( container ).toMatchSnapshot();
		} );
	} );
} );
