/**
 * External dependencies
 */
import configureStore from 'redux-mock-store';
import thunk from 'redux-thunk';

/**
 * Internal dependencies
 */
import { actions, thunks } from '@moderntribe/events/data/search';

const middlewares = [ thunk ];
const mockStore = configureStore( middlewares );

describe( '[STORE] - Search actions', () => {
	it( 'Should add a block', () => {
		expect( actions.addBlock( 1 ) ).toMatchSnapshot();
	} );

	it( 'Should set the term', () => {
		expect( actions.setTerm( 1, 'lorem' ) ).toMatchSnapshot();
	} );

	it( 'Should set the results', () => {
		expect( actions.setResults( 1, [ 2 ] ) ).toMatchSnapshot();
	} );

	it( 'Should add results', () => {
		expect( actions.addResults( 1, [ 2 ] ) ).toMatchSnapshot();
	} );

	it( 'Should set total pages', () => {
		expect( actions.setTotalPages( 1, 10 ) ).toMatchSnapshot();
	} );

	it( 'Should set the page', () => {
		expect( actions.setPage( 1, 2 ) ).toMatchSnapshot();
	} );

	it( 'Should enable loading', () => {
		expect( actions.enableSearchIsLoading( 1 ) ).toMatchSnapshot();
	} );

	it( 'Should disable loading', () => {
		expect( actions.disableSearchIsLoading( 1 ) ).toMatchSnapshot();
	} );

	it( 'Should clear the block', () => {
		expect( actions.clearBlock( 1 ) ).toMatchSnapshot();
	} );

	it( 'Should set the post type', () => {
		expect( actions.setSearchPostType( 1, 'post' ) ).toMatchSnapshot();
	} );
} );

describe( '[STORE] - Search thunk actions', () => {
	it( 'Should request action', () => {
		const store = mockStore( {
			events: {
				search: {
					post: {
						postType: 'tribe_events',
					},
				},
			}
		} );
		store.dispatch( thunks.search( 'post', { term: 'Modern' } ) );
		expect( store.getActions() ).toMatchSnapshot();
	} );

	it( 'Should clear the block', () => {
		const initialState = {
			events: {
				search: {},
			}
		};
		const store = mockStore( initialState );
		const searchParams = {
			term: '',
			populated: true,
		};

		store.dispatch( thunks.search( 'post', searchParams ) );
		expect( store.getActions() ).toMatchSnapshot();
	} );
} );

