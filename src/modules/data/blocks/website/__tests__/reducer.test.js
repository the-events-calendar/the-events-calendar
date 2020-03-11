/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/website';
import reducer, { setInitialState, DEFAULT_STATE } from '@moderntribe/events/data/blocks/website/reducer';

const entityRecord = {
	meta: {
		_EventURL: 'https://www.theeventscalendar.com/',
	},
};

describe( '[STORE] - Website reducer', () => {
	it( 'Should return the default state', () => {
		expect( reducer( undefined, {} ) ).toMatchSnapshot();
	} );

	it( 'Should set the website value', () => {
		expect( reducer( DEFAULT_STATE, actions.setWebsite( 'https://tri.be/' ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the initial state', () => {
		setInitialState( entityRecord );
		expect( DEFAULT_STATE ).toMatchSnapshot();
	} );
} );
