/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/organizers';

describe( '[STORE] - Organizers types', () => {
	it( 'Should return the organizers types', () => {
		expect( types.ADD_BLOCK_ORGANIZER ).toMatchSnapshot();
		expect( types.REMOVE_BLOCK_ORGANIZER ).toMatchSnapshot();
		expect( types.ADD_CLASSIC_ORGANIZERS ).toMatchSnapshot();
		expect( types.REMOVE_CLASSIC_ORGANIZERS ).toMatchSnapshot();
	} );
} );
