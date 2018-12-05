/**
 * External dependencies
 */
import configureStore from 'redux-mock-store';
import thunk from 'redux-thunk';

/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/website';

const middlewares = [ thunk ];
const mockStore = configureStore( middlewares );

describe( '[STORE] - Website actions', () => {
	it( 'Should set initial state', () => {
		expect( actions.setInitialState( {} ) ).toMatchSnapshot();
	} );

	it( 'Should set the website URL', () => {
		expect( actions.setWebsite( 'https://tri.be/' ) ).toMatchSnapshot();
	} );

	it( 'Should set the website label', () => {
		expect( actions.setLabel( 'Modern Tribe' ) ).toMatchSnapshot();
	} );
} );
