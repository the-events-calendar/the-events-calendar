/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/search';
import reducer, { DEFAULT_STATE } from '@moderntribe/events/data/search/reducers/search';

describe( '[STORE] - search reducer', () => {
	it( 'Should return default state', () => {
		expect( reducer( undefined, {} ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should return default state on add block', () => {
		expect( reducer( undefined, actions.addBlock( 'events' ) ) ).toMatchSnapshot();
	} );

	it( 'Should clear the block to the initial state keeping only the type', () => {
		const state = {
			...DEFAULT_STATE,
			results: [ 1, 2, 3 ],
			postType: 'tribe_events',
			isLoading: true,
		};
		expect( reducer( state, actions.clearBlock( 'events' ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the search term', () => {
		expect( reducer( DEFAULT_STATE, actions.setTerm( 'events', 'Modern Tribe' ) ) )
			.toMatchSnapshot();
	} );

	it( 'Should set the results', () => {
		expect( reducer( DEFAULT_STATE, actions.setResults( 'events', [ 1, 2, 3 ] ) ) )
			.toMatchSnapshot();
	} );

	it( 'Should add the results', () => {
		const state = {
			...DEFAULT_STATE,
			results: [ 1, 2, 3 ],
		};
		expect( reducer( state, actions.addResults( 'events', [ 4, 5, 6 ] ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the page', () => {
		expect( reducer( DEFAULT_STATE, actions.setPage( 'events', 2 ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the total pages', () => {
		expect( reducer( DEFAULT_STATE, actions.setTotalPages( 'events', 10 ) ) ).toMatchSnapshot();
	} );

	it( 'Should enable loading', () => {
		expect( reducer( DEFAULT_STATE, actions.enableSearchIsLoading( 'events' ) ) )
			.toMatchSnapshot();
		expect( reducer( DEFAULT_STATE, actions.disableSearchIsLoading( 'events' ) ) )
			.toMatchSnapshot();
	} );

	it( 'Should set the post type', () => {
		expect( reducer( DEFAULT_STATE, actions.setSearchPostType( 'events', 'tribe_organizer' ) ) )
			.toMatchSnapshot();
	} );
} );
