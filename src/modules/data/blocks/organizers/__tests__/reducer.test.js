/**
 * Internal dependencies
 */
import reducer, { setInitialState } from '@moderntribe/events/data/blocks/organizers/reducer';

const data = {
	meta: {
		_EventOrganizerID: [ 99, 100 ],
	},
};

describe( '[STORE] - Organizers reducer', () => {
	it( 'Should return the default state', () => {
		expect( reducer( undefined, {} ) ).toMatchSnapshot();
	} );

	it( 'Should set the initial state', () => {
		setInitialState( data );
		expect( reducer( undefined, {} ) ).toMatchSnapshot();
	} );
} );
