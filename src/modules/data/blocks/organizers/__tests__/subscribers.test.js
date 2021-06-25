/**
 * Internal dependencies
 */
import { globals } from '@moderntribe/common/utils';
import { store } from '@moderntribe/common/store';
import subscribe, {
	compareBlocks,
	isOrganizerBlock,
	handleBlockAdded,
	handleBlockRemoved,
	onBlocksChangeHandler,
	onBlocksChangeListener,
} from '@moderntribe/events/data/blocks/organizers/subscribers';
import { actions as formActions } from '@moderntribe/common/data/forms';
import {
	actions as organizerActions,
	selectors as organizerSelectors,
} from '@moderntribe/events/data/blocks/organizers';
import { selectors as detailSelectors } from '@moderntribe/events/data/details';

jest.mock( '@moderntribe/common/utils', () => {
	const dispatchModule = {
		editEntityRecord: jest.fn( ( kind, name, recordId, edits ) => {} ),
	};
	const selectModule = {
		getCurrentPostId: jest.fn( () => {} ),
		getBlocks: jest.fn( () => {} ),
	};

	return {
		__esModule: true,
		globals: {
			wpData: {
				dispatch: jest.fn( key => dispatchModule ),
				select: jest.fn( key => selectModule ),
				subscribe: jest.fn( ( listener ) => {} ),
			},
		},
	};
} );

jest.mock( '@moderntribe/common/store', () => {
	return {
		__esModule: true,
		store: {
			getState: jest.fn( () => ( {
				events: {
					blocks: {
						organizers: {
							blocks: {
								allIds: [ 99 ],
								byId: { 'organizer-1': 99 },
							},
							classic: [ 99 ],
						},
					},
				},
			} ) ),
			dispatch: jest.fn( ( action ) => {} ),
		},
	};
} );

jest.mock( '@moderntribe/common/data/forms', () => {
	return {
		__esModule: true,
		actions: {
			removeVolatile: jest.fn( ( organizer ) => {} ),
		},
	};
} );

jest.mock( '@moderntribe/events/data/details', () => {
	return {
		selectors: {
			getVolatile: jest.fn( ( state, props ) => false ),
		},
	};
} );

jest.mock( '@moderntribe/events/data/blocks/organizers', () => {
	return {
		__esModule: true,
		actions: {
			addOrganizerInBlock: jest.fn( ( clientId, organizer ) => {} ),
			removeOrganizerInBlock: jest.fn( ( clientId, organizer ) => {} ),
			removeOrganizerInClassic: jest.fn( ( organizer ) => {} ),
		},
		selectors: {
			getOrganizerByClientId: jest.fn( ( state, props ) => (
				state.events.blocks.organizers.blocks.byId[ props.clientId ]
			) ),
			getOrganizersInClassic: jest.fn( ( state ) => (
				state.events.blocks.organizers.classic
			) ),
		},
	};
} );

describe( '[STORE] - Organizers subscribers', () => {
	afterEach( () => {
		jest.clearAllMocks();
	} );

	it( 'Should return block client id', () => {
		const block = { clientId: 'clientId' };
		expect( compareBlocks( block ) ).toMatchSnapshot();
	} );

	it( 'Should determine if a block is an organizer block', () => {
		const organizerBlock = { name: 'tribe/event-organizer' };
		const priceBlock = { name: 'tribe/event-price' };
		expect( isOrganizerBlock( organizerBlock ) ).toMatchSnapshot();
		expect( isOrganizerBlock( priceBlock ) ).toMatchSnapshot();
	} );

	it( 'Should handle block addition', () => {
		const organizerBlockWithOrganizer = { name: 'tribe/event-organizer', attributes: { organizer: 99 } };
		const organizerBlockWithoutOrganizer = { name: 'tribe/event-organizer', attributes: { organizer: 0 } };
		const priceBlock = { name: 'tribe/event-price' };

		handleBlockAdded( priceBlock );
		expect( organizerActions.addOrganizerInBlock ).toHaveBeenCalledTimes( 0 );

		handleBlockAdded( organizerBlockWithoutOrganizer );
		expect( organizerActions.addOrganizerInBlock ).toHaveBeenCalledTimes( 0 );

		handleBlockAdded( organizerBlockWithOrganizer );
		expect( organizerActions.addOrganizerInBlock ).toHaveBeenCalledTimes( 1 );

		organizerActions.addOrganizerInBlock.mockClear();
	} );

	it( 'Should handle block removal: block is not organizer block', () => {
		const priceBlock = { name: 'tribe/event-price' };

		handleBlockRemoved( [] )( priceBlock );
		expect( organizerSelectors.getOrganizerByClientId ).toHaveBeenCalledTimes( 0 );
		expect( organizerActions.removeOrganizerInBlock ).toHaveBeenCalledTimes( 0 );
		expect( detailSelectors.getVolatile ).toHaveBeenCalledTimes( 0 );
		expect( organizerActions.removeOrganizerInClassic ).toHaveBeenCalledTimes( 0 );
		expect( formActions.removeVolatile ).toHaveBeenCalledTimes( 0 );
		expect( organizerSelectors.getOrganizersInClassic ).toHaveBeenCalledTimes( 0 );
		expect( globals.wpData.select( 'core/editor' ).getCurrentPostId ).toHaveBeenCalledTimes( 0 );
		expect( globals.wpData.dispatch( 'core' ).editEntityRecord ).toHaveBeenCalledTimes( 0 );
	} );

	it( 'Should handle block removal: organizer block has no organizer', () => {
		const organizerBlockWithoutOrganizer = {
			name: 'tribe/event-organizer',
			clientId: 'organizer-2',
			attributes: { organizer: 0 },
		};

		handleBlockRemoved( [] )( organizerBlockWithoutOrganizer );
		expect( organizerSelectors.getOrganizerByClientId ).toHaveBeenCalledTimes( 1 );
		expect( organizerActions.removeOrganizerInBlock ).toHaveBeenCalledTimes( 0 );
		expect( detailSelectors.getVolatile ).toHaveBeenCalledTimes( 1 );
		expect( organizerActions.removeOrganizerInClassic ).toHaveBeenCalledTimes( 1 );
		expect( formActions.removeVolatile ).toHaveBeenCalledTimes( 1 );
		expect( organizerSelectors.getOrganizersInClassic ).toHaveBeenCalledTimes( 1 );
		expect( globals.wpData.select( 'core/editor' ).getCurrentPostId ).toHaveBeenCalledTimes( 1 );
		expect( globals.wpData.dispatch( 'core' ).editEntityRecord ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'Should handle block removal: organizer block has organizer', () => {
		const organizerBlockWithOrganizer = {
			name: 'tribe/event-organizer',
			clientId: 'organizer-1',
			attributes: { organizer: 99 },
		};

		handleBlockRemoved( [] )( organizerBlockWithOrganizer );
		expect( organizerSelectors.getOrganizerByClientId ).toHaveBeenCalledTimes( 1 );
		expect( organizerActions.removeOrganizerInBlock ).toHaveBeenCalledTimes( 1 );
		expect( detailSelectors.getVolatile ).toHaveBeenCalledTimes( 1 );
		expect( organizerActions.removeOrganizerInClassic ).toHaveBeenCalledTimes( 1 );
		expect( formActions.removeVolatile ).toHaveBeenCalledTimes( 1 );
		expect( organizerSelectors.getOrganizersInClassic ).toHaveBeenCalledTimes( 1 );
		expect( globals.wpData.select( 'core/editor' ).getCurrentPostId ).toHaveBeenCalledTimes( 1 );
		expect( globals.wpData.dispatch( 'core' ).editEntityRecord ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'Should handle block removal: organizer block has organizer and classic block is in editor', () => {
		const organizerBlockWithOrganizer = {
			name: 'tribe/event-organizer',
			clientId: 'organizer-1',
			attributes: { organizer: 99 },
		};
		const classicBlock = { name: 'tribe/classic-event-details' };

		handleBlockRemoved( [ classicBlock ] )( organizerBlockWithOrganizer );
		expect( organizerSelectors.getOrganizerByClientId ).toHaveBeenCalledTimes( 1 );
		expect( organizerActions.removeOrganizerInBlock ).toHaveBeenCalledTimes( 1 );
		expect( detailSelectors.getVolatile ).toHaveBeenCalledTimes( 1 );
		expect( organizerActions.removeOrganizerInClassic ).toHaveBeenCalledTimes( 0 );
		expect( formActions.removeVolatile ).toHaveBeenCalledTimes( 0 );
		expect( organizerSelectors.getOrganizersInClassic ).toHaveBeenCalledTimes( 0 );
		expect( globals.wpData.select( 'core/editor' ).getCurrentPostId ).toHaveBeenCalledTimes( 0 );
		expect( globals.wpData.dispatch( 'core' ).editEntityRecord ).toHaveBeenCalledTimes( 0 );
	} );
} );
