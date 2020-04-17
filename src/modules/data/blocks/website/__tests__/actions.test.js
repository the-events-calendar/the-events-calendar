/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/website';

describe( '[STORE] - Website actions', () => {
	it( 'Should set the website URL', () => {
		expect( actions.setWebsite( 'https://tri.be/' ) ).toMatchSnapshot();
	} );
} );
