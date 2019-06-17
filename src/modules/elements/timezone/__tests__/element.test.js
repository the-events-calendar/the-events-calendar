/**
 * External dependencies
 */
import React from 'react';
import renderer from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { TimeZone } from '@moderntribe/events/elements';

describe( 'TimeZone element', () => {
	it( 'Should render the component', () => {
		const component = renderer.create( <TimeZone /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the value on the input', () => {
		const component = renderer.create( <TimeZone value="Modern Tribe" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the placeholder on the input', () => {
		const component = renderer.create( <TimeZone placeholder="Love this tribe ! " /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should add the class name into the component', () => {
		const component = renderer.create( <TimeZone className="my-custom-class-name" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should trigger the onChange event', () => {
		const onChange = jest.fn();
		const component = mount( <TimeZone value="Modern Tribe" onChange={ onChange } /> );
		component.find( 'input' ).simulate( 'change' );
		expect( onChange ).toHaveBeenCalled();
		expect( onChange ).toHaveBeenCalledWith( 'Modern Tribe' );
	} );
} );
