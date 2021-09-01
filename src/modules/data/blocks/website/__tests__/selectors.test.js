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
	},
};

describe( '[STORE] - Website selectors', () => {
	it( 'Should return the website block', () => {
		expect( selectors.getWebsiteBlock( state ) ).toMatchSnapshot();
	} );

	it( 'Should return the website url', () => {
		expect( selectors.getUrl( state ) ).toMatchSnapshot();
	} );
} );
