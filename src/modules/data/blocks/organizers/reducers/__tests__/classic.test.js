/**
 * Internal dependencies
 */
import classic, {
	setInitialState,
} from '@moderntribe/events/data/blocks/organizers/reducers/classic';
import { actions } from '@moderntribe/events/data/blocks/organizers';

const data = {
	meta: {
		_EventOrganizerID: [ 99, 100 ],
	},
};

describe( '[STORE] - Classic reducer', () => {
	it( 'Should return the default state', () => {
		expect( classic( undefined, {} ) ).toMatchSnapshot();
	} );

	it( 'Should add an organizer in classic', () => {
		expect( classic( [], actions.addOrganizerInClassic( 20 ) ) ).toMatchSnapshot();
		expect( classic( [ 20 ], actions.addOrganizerInClassic( 10 ) ) ).toMatchSnapshot();
	} );

	it( 'Should remove an organizer from block', () => {
		expect( classic( [ 20 ], actions.removeOrganizerInClassic( 20 ) ) ).toMatchSnapshot();
		expect( classic( [ 20, 10 ], actions.removeOrganizerInClassic( 10 ) ) ).toMatchSnapshot();
		expect( classic( [], actions.removeOrganizerInClassic( 99 ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the initial state', () => {
		setInitialState( data );
		expect( classic( undefined, {} ) ).toMatchSnapshot();
	} );
} );
