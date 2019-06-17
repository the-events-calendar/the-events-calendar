/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/website';
import { PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';

describe( '[STORE] - Website types', () => {
	it( 'Should match the types values', () => {
		expect( types.SET_WEBSITE_URL ).toBe( `${ PREFIX_EVENTS_STORE }/SET_WEBSITE_URL` );
		expect( types.SET_WEBSITE_LABEL ).toBe( `${ PREFIX_EVENTS_STORE }/SET_WEBSITE_LABEL` );
	} );
} );
