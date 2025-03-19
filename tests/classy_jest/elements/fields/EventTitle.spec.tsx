import {
	beforeAll,
	afterEach,
	beforeEach,
	describe,
	expect,
	jest,
	test,
} from '@jest/globals';
import { EventTitle } from '../../../../src/resources/packages/classy/elements';
import { render } from '@testing-library/react';
import { dispatch } from '@wordpress/data';
import { store } from '../../../../src/resources/packages/classy/store';
import {
	registerStoreIfNotRegistered,
	resetAllStores,
	registerMockStore,
	unregisterStore,
} from '../../__support__/store-mocks';

describe( 'EventTitle ', () => {
	beforeAll( () => {
		registerStoreIfNotRegistered( 'tec/classy', store );
	} );

	beforeEach( () => {
		global.mockWindowMatchMedia();
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
					getEditedPostAttribute( state, attribute: string ) {
						if ( attribute === 'title' ) {
							return '';
						}

						return null;
					},
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

						return null;
					},
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
			dispatch( 'tec/classy' ).editPost( { title: '' } );

			const { container } = render( <EventTitle title="Event Title" /> );

			expect( container ).toMatchSnapshot();
		} );

		test( 'initial state render with title for existing post', () => {
			// @ts-ignore
			dispatch( 'tec/classy' ).editPost( { title: 'Classy title' } );

			const { container } = render( <EventTitle title="Event Title" /> );

			expect( container ).toMatchSnapshot();
		} );
	} );
} );
