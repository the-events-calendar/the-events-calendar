/**
 * Internal dependencies
 */
import { allIds, byId } from '@moderntribe/events/data/blocks/organizers/reducers/blocks';
import block from '@moderntribe/events/data/blocks/organizers/reducers/block';
import { actions } from '@moderntribe/events/data/blocks/organizers';

jest.mock( '@moderntribe/events/data/blocks/organizers/reducers/block', () => {
	const original = require.requireActual( '@moderntribe/events/data/search/reducers/search' );
	return {
		__esModule: true,
		...original,
		default: jest.fn( ( state = original.DEFAULT_STATE ) => state ),
	};
} );

describe( '[STORE] - Organizer allIDs reducer', () => {
	test( 'It should return the default state', () => {
		expect( allIds( undefined, {} ) ).toEqual( [] );
	} );

	test( 'Add organizer block', () => {
		expect( allIds( [], actions.addOrganizerInBlock( 99, 1 ) ) ).toEqual( [ 1 ] );
		expect( allIds( [ 1 ], actions.addOrganizerInBlock( 100, 2 ) ) ).toEqual( [ 1, 2 ] );
	} );

	test( 'Remove organizer block', () => {
		expect( allIds( [], actions.removeOrganizerInBlock( 102, 3 ) ) ).toEqual( [] );
		expect( allIds( [ 1, 2 ], actions.removeOrganizerInBlock( 100, 2 ) ) ).toEqual( [ 1 ] );
		expect( allIds( [ 1 ], actions.removeOrganizerInBlock( 99, 1 ) ) ).toEqual( [] );
	} );
} );

describe( '[STORE] - Organizer byId reducer', () => {
	test( 'It should return the default state', () => {
		expect( byId( undefined, {} ) ).toEqual( {} );
	} );


	it( 'Should pass the actions to the child reducer when block not present', () => {
		const groupAction = [
			actions.addOrganizerInBlock( 10, 99 ),
			actions.removeOrganizerInBlock( 10, 99 ),
		];

		groupAction.forEach( ( action ) => {
			byId( {}, action );
			expect( block ).toHaveBeenCalledWith( undefined, action );
			expect( block ).toHaveBeenCalledTimes( 1 );
			block.mockClear();
		} );
	} );

	it( 'It should pass the block to the child reducer', () => {
		const groupAction = [
			actions.addOrganizerInBlock( 10, 99 ),
			actions.removeOrganizerInBlock( 10, 99 ),
		];

		const state = {
			10: {},
		};
		groupAction.forEach( ( action ) => {
			byId( state, action );
			expect( block ).toHaveBeenCalledWith( {}, action );
			expect( block ).toHaveBeenCalledTimes( 1 );
			block.mockClear();
		} );
	} );
} );
