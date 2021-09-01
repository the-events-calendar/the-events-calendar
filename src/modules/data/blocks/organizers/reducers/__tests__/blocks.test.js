/**
 * Internal dependencies
 */
import blocks, { allIds, byId } from '@moderntribe/events/data/blocks/organizers/reducers/blocks';
import { actions } from '@moderntribe/events/data/blocks/organizers';

describe( '[STORE] - Organizer allIDs reducer', () => {
	test( 'Should return the default state', () => {
		expect( allIds( undefined, {} ) ).toMatchSnapshot();
	} );

	test( 'Should add organizer block', () => {
		expect( allIds( [], actions.addOrganizerInBlock( 99, 1 ) ) ).toMatchSnapshot();
		expect( allIds( [ 1 ], actions.addOrganizerInBlock( 100, 2 ) ) ).toMatchSnapshot();
	} );

	test( 'Should remove organizer block', () => {
		expect( allIds( [], actions.removeOrganizerInBlock( 102, 3 ) ) ).toMatchSnapshot();
		expect( allIds( [ 1, 2 ], actions.removeOrganizerInBlock( 100, 2 ) ) ).toMatchSnapshot();
		expect( allIds( [ 1 ], actions.removeOrganizerInBlock( 99, 1 ) ) ).toMatchSnapshot();
	} );
} );

describe( '[STORE] - Organizer byId reducer', () => {
	test( 'Should return the default state', () => {
		expect( byId( undefined, {} ) ).toEqual( {} );
	} );

	it( 'Should add organizer block', () => {
		expect( byId( {}, actions.addOrganizerInBlock( 99, 1 ) ) ).toMatchSnapshot();
		expect( byId( { 99: 1 }, actions.addOrganizerInBlock( 100, 2 ) ) ).toMatchSnapshot();
	} );

	it( 'Should remove organizer block', () => {
		expect( byId( {}, actions.removeOrganizerInBlock( 102, 3 ) ) ).toMatchSnapshot();
		expect( byId( { 99: 1, 100: 2 }, actions.removeOrganizerInBlock( 100, 2 ) ) ).toMatchSnapshot();
		expect( byId( { 99: 1 }, actions.removeOrganizerInBlock( 99, 1 ) ) ).toMatchSnapshot();
	} );
} );

describe( '[STORE] - Organizer blocks reducer', () => {
	test( 'Should return the default state', () => {
		expect( blocks( undefined, {} ) ).toMatchSnapshot();
	} );
} );
