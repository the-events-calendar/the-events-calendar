/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/organizers';

describe( '[STORE] - Organizer actions', () => {
	test( 'Add organizer in classic', () => {
		expect( actions.addOrganizerInClassic( 77 ) ).toMatchSnapshot();
	} );

	test( 'Add organizer in a block', () => {
		expect( actions.addOrganizerInBlock( 'firstBlock', 99 ) ).toMatchSnapshot();
	} );

	test( 'Set organizer in classic', () => {
		expect( actions.setOrganizersInClassic( [ 1, 2, 3 ] ) ).toMatchSnapshot();
	} );

	test( 'Remove organizer in classic', () => {
		expect( actions.removeOrganizerInClassic( 2 ) ).toMatchSnapshot();
	} );

	test( 'Remove organizer in block', () => {
		expect( actions.removeOrganizerInBlock( 'firstBlock', 100 ) ).toMatchSnapshot();
	} );
} );
