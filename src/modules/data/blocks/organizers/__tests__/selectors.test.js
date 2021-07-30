/**
 * Internal dependencies
 */
import { selectors } from '@moderntribe/events/data/blocks/organizers';

const state = {
	events: {
		blocks: {
			organizers: {
				blocks: {
					byId: {
						firstBlock: {
							organizer: 100,
						},
						secondBlock: {
							organizer: 101,
						},
					},
					allIds: [ 100, 101 ],
				},
				classic: [ 98, 99, 100 ],
			},
		},
	},
};

describe( '[STORE] - Organizers selectors', () => {
	it( 'Should return the classic organizers', () => {
		expect( selectors.getOrganizersInClassic( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the organizer by clientId', () => {
		expect( selectors.getOrganizerByClientId( state, { clientId: 'firstBlock' } ) )
			.toMatchSnapshot();
		expect( selectors.getOrganizerByClientId( state, { clientId: 'secondBlock' } ) )
			.toMatchSnapshot();
		expect( selectors.getOrganizerByClientId( state, { clientId: 'thirdBlock' } ) )
			.toMatchSnapshot();
	} );

	it( 'Should return the organizers in a block', () => {
		expect( selectors.getOrganizersInBlock( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the mapped organizers', () => {
		expect( selectors.getMappedOrganizers( state ) ).toMatchSnapshot();
	} );
} );
