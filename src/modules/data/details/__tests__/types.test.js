/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/details';
import { PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';

describe( '[STORE] - Details types', () => {
	it( 'Should return the types values', () => {
		expect( types.SET_DETAILS_POST_TYPE ).toBe( `${ PREFIX_EVENTS_STORE }/SET_DETAILS_POST_TYPE` );
		expect( types.SET_DETAILS_IS_LOADING )
			.toBe( `${ PREFIX_EVENTS_STORE }/SET_DETAILS_IS_LOADING` );
		expect( types.SET_DETAILS ).toBe( `${ PREFIX_EVENTS_STORE }/SET_DETAILS` );
	} );
} );
