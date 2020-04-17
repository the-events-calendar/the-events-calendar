/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/blocks/price';

describe( '[STORE] - Price types', () => {
	it( 'Should return the types values', () => {
		expect( types.SET_PRICE_POSITION ).toMatchSnapshot();
		expect( types.SET_PRICE_SYMBOL ).toMatchSnapshot();
		expect( types.SET_PRICE_COST ).toMatchSnapshot();
	} );
} );
