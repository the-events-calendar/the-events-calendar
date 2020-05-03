/**
 * Internal dependencies
 */
import { utils } from '@moderntribe/events/data/blocks/price';

describe( '[STORE] - Price utils', () => {
	it( 'Should return the position', () => {
		expect( utils.getPosition( true ) ).toMatchSnapshot();
		expect( utils.getPosition( false ) ).toMatchSnapshot();
	} );
} );
