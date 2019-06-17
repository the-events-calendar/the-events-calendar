/**
 * Internal dependencies
 */
import { EVENT, ORGANIZER, VENUE } from '@moderntribe/common/data/editor/post-types';

describe( 'Tests for post-types.js', () => {
	test( 'It should match the TEC post types', () => {
		expect( EVENT ).toEqual( 'tribe_events' );
		expect( ORGANIZER ).toEqual( 'tribe_organizer' );
		expect( VENUE ).toEqual( 'tribe_venue' );
	} );
} );
