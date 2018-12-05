/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/organizers';
import { PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';

describe( '[STORE] - Organizers types', () => {
	it( 'Should return the organizers types', () => {
		expect( types.ADD_ORGANIZER_BLOCK ).toBe( `${ PREFIX_EVENTS_STORE }/ADD_ORGANIZER_BLOCK` );
		expect( types.REMOVE_ORGANIZER_BLOCK )
			.toBe( `${ PREFIX_EVENTS_STORE }/REMOVE_ORGANIZER_BLOCK` );
		expect( types.ADD_CLASSIC_ORGANIZERS )
			.toBe( `${ PREFIX_EVENTS_STORE }/ADD_CLASSIC_ORGANIZERS` );
		expect( types.SET_CLASSIC_ORGANIZERS )
			.toBe( `${ PREFIX_EVENTS_STORE }/SET_CLASSIC_ORGANIZERS` );
		expect( types.REMOVE_CLASSIC_ORGANIZERS )
			.toBe( `${ PREFIX_EVENTS_STORE }/REMOVE_CLASSIC_ORGANIZERS` );
	} );
} );
