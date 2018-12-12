/**
 * External dependencies
 */
import renderer from 'react-test-renderer';
import React from 'react';
import configureStore from 'redux-mock-store';
import thunk from 'redux-thunk';

/**
 * Internal dependencies
 */
import { withDetails } from '@moderntribe/events/hoc';

const initialState = {
	events: {
		details: {},
	},
	forms: {
		byID: {},
		volatile: [],
	},
};
// here it is possible to pass in any middleware if needed into //configureStore
const mockStore = configureStore( [ thunk ] );
const store = mockStore( initialState );

const Block = () => <div>With Details!</div>;
let Wrapper;
let component;
let instance;

describe( 'HOC - With Details', () => {
	beforeEach( () => {
		Wrapper = withDetails()( Block );
		component = renderer.create( <Wrapper store={ store } clientId="event" /> );
		instance = component.root;
	} );

	afterEach( () => {
		mockStore( initialState );
		store.clearActions();
	} );

	it( 'Should render a component', () => {
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the inner component', () => {
		expect( instance ).not.toBe( null );
		expect( () => instance.findByType( Block ) ).not.toThrowError();
	} );

	it( 'Should attach the details properties', () => {
		const expected = {
			details: {},
			isLoading: false,
		};
		expect( instance.findByType( Block ).props ).toMatchObject( expected );
	} );

	it( 'Should match the dispatched actions', () => {
		expect( store.getActions() ).toMatchSnapshot();
	} );
} );

