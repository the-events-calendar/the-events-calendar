/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/classic';
import { PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';

describe( '[STORE] - Classic types', () => {
	it( 'Should return the types values', () => {
		expect( types.SET_CLASSIC_ORGANIZERS_TITLE )
			.toBe( `${ PREFIX_EVENTS_STORE }/SET_CLASSIC_ORGANIZERS_TITLE` );
		expect( types.SET_CLASSIC_DETAILS_TITLE )
			.toBe( `${ PREFIX_EVENTS_STORE }/SET_CLASSIC_DETAILS_TITLE` );
	} );
} );
