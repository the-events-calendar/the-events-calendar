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
	}
};

describe( '[STORE] - Price selectors', () => {
	it( 'Should return the price block', () => {
		expect( selectors.getPriceBlock( state ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should return price position', () => {
		expect( selectors.getPosition( state ) ).toEqual( DEFAULT_STATE.position );
	} );

	it( 'Should return the price value', () => {
		expect( selectors.getPrice( state ) ).toEqual( DEFAULT_STATE.cost );
	} );

	it( 'Should return the price description', () => {
		expect( selectors.getDescription( state ) ).toEqual( DEFAULT_STATE.description );
	} );

	it( 'Should return the price symbol', () => {
		expect( selectors.getSymbol( state ) ).toEqual( DEFAULT_STATE.symbol );
	} );
} );
