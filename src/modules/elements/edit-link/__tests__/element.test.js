/**
 * External dependencies
 */
import renderer from 'react-test-renderer';
import React from 'react';

/**
 * Internal dependencies
 */
import { EditLink } from '@moderntribe/events/elements';

describe( 'EditLink element', () => {
	beforeAll( () => {
		window.tribe_js_config = {
			admin_url: 'http://localhost//wp-admin/',
		}
	} );

	it( 'Should not render with missing required props', () => {
		const component = renderer.create( <EditLink /> );
		expect( component.toJSON() ).toBe( null );
	} );

	it( 'Should render the component', () => {
		const component = renderer.create( <EditLink postId={ 100 } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should set the target attribute', () => {
		const component = renderer.create( <EditLink postId={ 101 } target="_self" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should set the label for the edit link', () => {
		const component = renderer.create( <EditLink postId={ 102 } label="Modern Tribe" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	afterAll( () => {
		delete window.tribe_js_config;
	});
} );
