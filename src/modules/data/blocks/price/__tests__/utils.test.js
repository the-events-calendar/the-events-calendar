/**
 * Internal dependencies
 */
import * as utils from '@moderntribe/events/data/blocks/price/utils';

describe( '[STORE] - Price utils', () => {
	it( 'Should get position', () => {
		expect( utils.getPosition( true ) ).toMatchSnapshot();
		expect( utils.getPosition( false ) ).toMatchSnapshot();
	} );
} );
