/**
 * External dependencies
 */
import configureStore from 'redux-mock-store';
import thunk from 'redux-thunk';
/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/events/data/blocks/classic';

const middlewares = [ thunk ];
const mockStore = configureStore( middlewares );

describe( '[STORE] - Classic actions', () => {
	it( 'Should set initial state', () => {
		expect( actions.setInitialState( {} ) ).toMatchSnapshot();
	} );

	test( 'Action to set the organizer title', () => {
		expect( actions.setOrganizerTitle( 'Modern Tribe' ) ).toMatchSnapshot();
	} );

	test( 'Action to set the details title', () => {
		expect( actions.setDetailsTitle( 'Events' ) ).toMatchSnapshot();
	} );
} );
