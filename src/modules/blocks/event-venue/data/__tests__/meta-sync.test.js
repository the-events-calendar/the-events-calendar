/**
 * Internal dependencies
 */
import { syncVenuesWithPost } from '../meta-sync';

jest.mock( '@moderntribe/common/utils/globals', () => {
	const dispatchModule = {
		editEntityRecord: jest.fn(),
	};
	const selectModule = {
		getCurrentPostId: jest.fn( () => 1 ),
		getCurrentPostType: jest.fn( () => 'tribe_events' ),
	};

	return {
		__esModule: true,
		wpData: {
			dispatch: jest.fn( () => dispatchModule ),
			select: jest.fn( () => selectModule ),
		},
	};
} );

jest.mock( '@moderntribe/events/data/blocks/venue', () => {
	return {
		__esModule: true,
		selectors: {
			getVenuesInBlock: jest.fn( () => [ 42 ] ),
		},
	};
} );

jest.mock( '@moderntribe/common/store', () => {
	return {
		__esModule: true,
		store: {
			getState: jest.fn( () => ( {} ) ),
		},
	};
} );

jest.mock( '@moderntribe/common/data', () => {
	return {
		__esModule: true,
		editor: {
			EVENT: 'tribe_events',
			VENUE: 'tribe_venue',
			ORGANIZER: 'tribe_organizer',
		},
	};
} );

const { wpData } = require( '@moderntribe/common/utils/globals' );

describe( '[BLOCK] - Event Venue meta-sync', () => {
	afterEach( () => {
		jest.clearAllMocks();
	} );

	it( 'Should sync venue ID to post meta when current post type is tribe_events', () => {
		syncVenuesWithPost();

		expect( wpData.select( 'core/editor' ).getCurrentPostType ).toHaveBeenCalledTimes( 1 );
		expect( wpData.select( 'core/editor' ).getCurrentPostId ).toHaveBeenCalledTimes( 1 );
		expect( wpData.dispatch( 'core' ).editEntityRecord ).toHaveBeenCalledTimes( 1 );
		expect( wpData.dispatch( 'core' ).editEntityRecord ).toHaveBeenCalledWith(
			'postType',
			'tribe_events',
			1,
			{ meta: { _EventVenueID: [ 42 ] } }
		);
	} );

	it( 'Should not call editEntityRecord when current post type is not tribe_events (e.g. FSE site editor)', () => {
		wpData.select( 'core/editor' ).getCurrentPostType.mockReturnValueOnce( 'wp_template' );

		syncVenuesWithPost();

		expect( wpData.select( 'core/editor' ).getCurrentPostType ).toHaveBeenCalledTimes( 1 );
		expect( wpData.select( 'core/editor' ).getCurrentPostId ).toHaveBeenCalledTimes( 0 );
		expect( wpData.dispatch( 'core' ).editEntityRecord ).toHaveBeenCalledTimes( 0 );
	} );
} );
