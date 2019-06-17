/**
 * Internal dependencies
 */
import reducer, { actions } from '@moderntribe/events/data/search';
import search, { DEFAULT_STATE } from '@moderntribe/events/data/search/reducers/search';


jest.mock( '@moderntribe/events/data/search/reducers/search', () => {
	const original = require.requireActual( '@moderntribe/events/data/search/reducers/search' );
	return {
		__esModule: true,
		...original,
		default: jest.fn( ( state = original.DEFAULT_STATE ) => state ),
	};
} );

describe( '[STORE] - search reducers', () => {
	beforeEach( () => {
		search.mockClear();
	} );

	it( 'Should return default state', () => {
		expect( reducer( undefined, {} ) ).toEqual( {} );
	} );

	it( 'Should add a new block', () => {
		expect( reducer( {}, actions.addBlock( 'events' ) ) ).toEqual( { events: DEFAULT_STATE } );
	} );

	it( 'Should pass the actions to the child reducer when block not present', () => {
		const groupAction = [
			actions.addBlock( 'post' ),
			actions.clearBlock( 'post' ),
			actions.setTerm( 'post', {} ),
			actions.setResults( 'post', [] ),
			actions.addResults( 'post', [] ),
			actions.setPage( 'post', 2 ),
			actions.setTotalPages( 'post', 3 ),
			actions.enableSearchIsLoading( 'post' ),
			actions.setSearchPostType( 'post', 'posts' ),
		];

		groupAction.forEach( ( action ) => {
			reducer( {}, action );
			expect( search ).toHaveBeenCalledWith( undefined, action );
			expect( search ).toHaveBeenCalledTimes( 1 );
			search.mockClear();
		} );
	} );

	it( 'It should pass the block to the child reducer', () => {
		const groupAction = [
			actions.addBlock( 'events' ),
			actions.clearBlock( 'events' ),
			actions.setTerm( 'events', {} ),
			actions.setResults( 'events', [] ),
			actions.addResults( 'events', [] ),
			actions.setPage( 'events', 2 ),
			actions.setTotalPages( 'events', 3 ),
			actions.enableSearchIsLoading( 'events' ),
			actions.setSearchPostType( 'events', 'tribe_events' ),
		];

		const state = {
			events: {},
		};
		groupAction.forEach( ( action ) => {
			reducer( state, action );
			expect( search ).toHaveBeenCalledWith( {}, action );
			expect( search ).toHaveBeenCalledTimes( 1 );
			search.mockClear();
		} );
	} );
} );
