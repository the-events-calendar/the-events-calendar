/**
 * Internal dependencies
 */
import { selectors } from '@moderntribe/events/data/blocks/website';
import { DEFAULT_STATE } from '@moderntribe/events/data/blocks/website/reducer';

const state = {
	events: {
		blocks: {
			website: DEFAULT_STATE,
		},
	}
};

describe( '[STORE] - Website selectors', () => {
	it( 'Should return the website block', () => {
		expect( selectors.getWebsiteBlock( state ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should return the website label', () => {
		expect( selectors.getLabel( state ) ).toEqual( DEFAULT_STATE.label );
	} );

	it( 'Should return the website url', () => {
		expect( selectors.getUrl( state ) ).toEqual( DEFAULT_STATE.url );
	} );
} );
