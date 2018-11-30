/**
 * Internal dependencies
 */
import { types } from '@moderntribe/events/data/search';
import { PREFIX_EVENTS_STORE } from '@moderntribe/events/data/utils';

describe( '[STORE] - Search types', () => {
	it( 'Should return the types values', () => {
		expect( types.SET_SEARCH_POST_TYPE ).toBe( `${ PREFIX_EVENTS_STORE }/SET_SEARCH_POST_TYPE` );
		expect( types.ADD_BLOCK ).toBe( `${ PREFIX_EVENTS_STORE }/ADD_BLOCK` );
		expect( types.CLEAR_BLOCK ).toBe( `${ PREFIX_EVENTS_STORE }/CLEAR_BLOCK` );
		expect( types.SET_SEARCH_IS_LOADING ).toBe( `${ PREFIX_EVENTS_STORE }/SET_SEARCH_IS_LOADING` );
		expect( types.SET_PAGE ).toBe( `${ PREFIX_EVENTS_STORE }/SET_PAGE` );
		expect( types.SET_TOTAL_PAGES ).toBe( `${ PREFIX_EVENTS_STORE }/SET_TOTAL_PAGES` );
		expect( types.ADD_RESULTS ).toBe( `${ PREFIX_EVENTS_STORE }/ADD_RESULTS` );
		expect( types.SET_RESULTS ).toBe( `${ PREFIX_EVENTS_STORE }/SET_RESULTS` );
		expect( types.SET_TERM ).toBe( `${ PREFIX_EVENTS_STORE }/SET_TERM` );
		expect( types.SEARCH ).toBe( `${ PREFIX_EVENTS_STORE }/SEARCH` );
	} );
} );
