/**
 * Internal dependencies
 */
import { selectors } from '@moderntribe/events/data/blocks/price';
import { DEFAULT_STATE } from '@moderntribe/events/data/blocks/price/reducer';

const state = {
	events: {
		blocks: {
			price: DEFAULT_STATE,
		},
	},
};

describe( '[STORE] - Price selectors', () => {
	it( 'Should return the price block', () => {
		expect( selectors.getPriceBlock( state ) ).toMatchSnapshot();
	} );

	it( 'Should return price position', () => {
		expect( selectors.getPosition( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the price value', () => {
		expect( selectors.getPrice( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the price symbol', () => {
		expect( selectors.getSymbol( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the price code', () => {
		expect( selectors.getCode( state ) ).toMatchSnapshot();
	} );
} );
