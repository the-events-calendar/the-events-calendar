/**
 * Internal dependencies
 */
import * as types from '@moderntribe/events/data/blocks/price/types';
import { PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';

describe( '[STORE] - Price types', () => {
	it( 'Should return the types values', () => {
		expect( types.SET_PRICE_POSITION ).toBe( `${ PREFIX_EVENTS_STORE }/SET_PRICE_POSITION` );
		expect( types.SET_PRICE_SYMBOL ).toBe( `${ PREFIX_EVENTS_STORE }/SET_PRICE_SYMBOL` );
		expect( types.SET_PRICE_COST ).toBe( `${ PREFIX_EVENTS_STORE }/SET_PRICE_COST` );
	} );
} );
