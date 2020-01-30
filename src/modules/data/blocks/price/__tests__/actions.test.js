/**
 * Internal dependencies
 */
import * as actions from '@moderntribe/events/data/blocks/price/actions';

describe( '[STORE] - Price actions', () => {
	it( 'Should set initial state', () => {
		expect( actions.setInitialState( {} ) ).toMatchSnapshot();
	} );
	it( 'Should set the cost', () => {
		expect( actions.setCost( 10 ) ).toMatchSnapshot();
	} );

	it( 'Should set the position of the symbol', () => {
		expect( actions.setPosition( 'suffix' ) ).toMatchSnapshot();
	} );

	it( 'Should set the symbol', () => {
		expect( actions.setSymbol( '€' ) ).toMatchSnapshot();
	} );

	it( 'Should toggle the prefix', () => {
		expect( actions.togglePosition( true ) ).toMatchSnapshot();
		expect( actions.togglePosition( false ) ).toMatchSnapshot();
	} );
} );
