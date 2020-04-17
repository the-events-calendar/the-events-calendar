/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/website';
import reducer, {
	DEFAULT_STATE,
	defaultStateToMetaMap,
	setInitialState,
} from '@moderntribe/events/data/blocks/website/reducer';

const data = {
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

	it( 'Should return the default state to meta map', () => {
		expect( defaultStateToMetaMap ).toMatchSnapshot();
	} );

	it( 'Should set the initial state', () => {
		setInitialState( data );
		expect( DEFAULT_STATE ).toMatchSnapshot();
	} );
} );
