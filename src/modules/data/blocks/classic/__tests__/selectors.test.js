/**
 * Internal dependencies
 */
import { selectors } from '@moderntribe/events/data/blocks/classic';
import { DEFAULT_STATE } from '@moderntribe/events/data/blocks/classic/reducer';

const state = {
	events: {
		blocks: {
			classic: DEFAULT_STATE,
		},
	},
};

describe( '[STORE] - Classic selectors', () => {
	it( 'Should return the block', () => {
		expect( selectors.classicSelector( state ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should return the details title', () => {
		expect( selectors.detailsTitleSelector( state ) ).toBe( DEFAULT_STATE.detailsTitle );
	} );

	it( 'Should return the organizer title', () => {
		expect( selectors.organizerTitleSelector( state ) ).toBe( DEFAULT_STATE.organizerTitle );
	} );
} );
