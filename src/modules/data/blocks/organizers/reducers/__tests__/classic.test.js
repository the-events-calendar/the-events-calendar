/**
 * Internal dependencies
 */
import classic from '@moderntribe/events/data/blocks/organizers/reducers/classic';
import { actions } from '@moderntribe/events/data/blocks/organizers';

describe( '[STORE] - Classic reducer', () => {
	it( 'Should return the default state', () => {
		expect( classic( undefined, {} ) ).toEqual( [] );
	} );

	it( 'Should add an organizer in classic', () => {
		expect( classic( [], actions.addOrganizerInClassic( 20 ) ) ).toEqual( [ 20 ] );
		expect( classic( [ 20 ], actions.addOrganizerInClassic( 10 ) ) ).toEqual( [ 20, 10 ] );
	} );

	it( 'Should remove an organizer from block', () => {
		expect( classic( [ 20 ], actions.removeOrganizerInClassic( 20 ) ) ).toEqual( [] );
		expect( classic( [ 20, 10 ], actions.removeOrganizerInClassic( 10 ) ) ).toEqual( [ 20 ] );
		expect( classic( [], actions.removeOrganizerInClassic( 99 ) ) ).toEqual( [] );
	} );

	it( 'Should set the classic organizers', () => {
		expect( classic( [], actions.setOrganizersInClassic( [ 1, 2, 3 ] ) ) ).toEqual( [ 1, 2, 3 ] );
		expect( classic( [ 1 ], actions.setOrganizersInClassic( [ 2, 3 ] ) ) ).toEqual( [ 2, 3 ] );
	} );
} );
