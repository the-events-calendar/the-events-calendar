/**
 * Internal dependencies
 */
import block, { DEFAULT_STATE } from '@moderntribe/events/data/blocks/organizers/reducers/block';
import { actions } from '@moderntribe/events/data/blocks/organizers';

describe( '[STORE] - Block reducer', () => {
	it( 'Should return the default state', () => {
		expect( block( undefined, {} ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should add an organizer in block', () => {
		expect( block( {}, actions.addOrganizerInBlock( 20, 10 ) ) ).toEqual( { organizer: 10 } );
	} );

	it( 'Should remove an organizer from block', () => {
		expect( block( { organizer: 10 }, actions.removeOrganizerInBlock( 20, 10 ) ) )
			.toEqual( DEFAULT_STATE );
	} );
} );
