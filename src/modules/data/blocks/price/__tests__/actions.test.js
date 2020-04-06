/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/price';

describe( '[STORE] - Price actions', () => {
	it( 'Should set the cost', () => {
		expect( actions.setCost( 10 ) ).toMatchSnapshot();
	} );

	it( 'Should set the position of the symbol', () => {
		expect( actions.setPosition( 'suffix' ) ).toMatchSnapshot();
	} );

	it( 'Should set the symbol', () => {
		expect( actions.setSymbol( '€' ) ).toMatchSnapshot();
	} );
} );
