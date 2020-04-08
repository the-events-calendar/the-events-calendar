/**
 * Internal dependencies
 */
import reducer, { setInitialState } from '@moderntribe/events/data/blocks/organizers/reducer';

const entityRecord = {
	meta: {
		_EventOrganizerID: [ 99, 100 ],
	},
};

describe( '[STORE] - Organizers reducer', () => {
	it( 'Should return the default state', () => {
		expect( reducer( undefined, {} ) ).toMatchSnapshot();
	} );

	it( 'Should set the initial state', () => {
		setInitialState( entityRecord );
		expect( reducer( undefined, {} ) ).toMatchSnapshot();
	} );
} );
