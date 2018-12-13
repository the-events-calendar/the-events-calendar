/**
 * Internal dependencies
 */
import { selectors } from '@moderntribe/events/data/search';
import { DEFAULT_STATE } from '@moderntribe/events/data/search/reducers/search';

const state = {
	events: {
		search: {
			test: DEFAULT_STATE,
		},
	},
};

const props = {
	name: 'test',
};

describe( '[STORE] - Search selectors', () => {
	it( 'Should return search block', () => {
		expect( selectors.blockSelector( state, props ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should return the post type of the block', () => {
		expect( selectors.getSearchPostType( state, props ) ).toEqual( DEFAULT_STATE.postType );
	} );

	it( 'Should return the search term', () => {
		expect( selectors.getSearchTerm( state, props ) ).toEqual( DEFAULT_STATE.term );
	} );

	it( 'Should return the isLoading state', () => {
		expect( selectors.getIsLoading( state, props ) ).toEqual( DEFAULT_STATE.isLoading );
	} );

	it( 'Should return the search results', () => {
		expect( selectors.getResults( state, props ) ).toEqual( DEFAULT_STATE.results );
	} );

	it( 'Should return the search page', () => {
		expect( selectors.getPage( state, props ) ).toEqual( DEFAULT_STATE.page );
	} );

	it( 'Should return the total results page', () => {
		expect( selectors.getTotal( state, props ) ).toEqual( DEFAULT_STATE.totalPages );
	} );
} );
