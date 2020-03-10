/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/website';

describe( '[STORE] - Website types', () => {
	it( 'Should match the types values', () => {
		expect( types.SET_WEBSITE_URL ).toMatchSnapshot();
	} );
} );
