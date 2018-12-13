/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/website';
import reducer, { DEFAULT_STATE } from '@moderntribe/events/data/blocks/website/reducer';

describe( '[STORE] - Website reducer', () => {
	it( 'Should return the default state', () => {
		expect( reducer( undefined, {} ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should set the website value', () => {
		expect( reducer( DEFAULT_STATE, actions.setWebsite( 'https://tri.be/' ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the website label', () => {
		expect( reducer( DEFAULT_STATE, actions.setLabel( 'Modern Tribe' ) ) ).toMatchSnapshot();
	} );
} );
