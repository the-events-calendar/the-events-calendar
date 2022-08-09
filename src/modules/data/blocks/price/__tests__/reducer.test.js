/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/price';
import reducer, {
	DEFAULT_STATE,
	defaultStateToMetaMap,
	setInitialState,
} from '@moderntribe/events/data/blocks/price/reducer';

const data = {
	meta: {
		_EventCurrencyPosition: 'prefix',
		_EventCurrencySymbol: '€',
		_EventCurrencyCode: 'EUR',
		_EventCost: '15',
	},
};

describe( '[STORE] - Price reducer', () => {
	it( 'Should return the default state', () => {
		expect( reducer( undefined, {} ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should set the cost', () => {
		expect( reducer( DEFAULT_STATE, actions.setCost( 10 ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the symbol position', () => {
		expect( reducer( DEFAULT_STATE, actions.setPosition( 'prefix' ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the cost symbol', () => {
		expect( reducer( DEFAULT_STATE, actions.setSymbol( '€' ) ) ).toMatchSnapshot();
	} );

	it( 'Should set the cost code', () => {
		expect( reducer( DEFAULT_STATE, actions.setCode( 'EUR' ) ) ).toMatchSnapshot();
	} );

	it( 'Should return the default state to meta map', () => {
		expect( defaultStateToMetaMap ).toMatchSnapshot();
	} );

	it( 'Should set the initial state', () => {
		setInitialState( data );
		expect( DEFAULT_STATE ).toMatchSnapshot();
	} );
} );
