/**
 * External dependencies
 */
import React from 'react';
import toJson from 'enzyme-to-json';

/**
 * Internal dependencies
 */
import OrganizerForm from '../element';

describe( 'OrganizerForm', () => {
	const addOrganizer = jest.fn();
	const onClose = jest.fn();
	let state;

	beforeEach( () => {
		state = {
			title: '',
			email: '',
			phone: '',
			website: '',
		};
	} );

	test( 'should show a spinner while creating', () => {
		const component = shallow(
			<OrganizerForm addOrganizer={ addOrganizer } onClose={ onClose } />,
		);
		component.instance().isCreating = jest.fn( () => true );
		component.instance().forceUpdate();
		expect( toJson( component ) ).toMatchSnapshot();
	} );

	test( 'should be set as invalid when any field validation fails', () => {
		const component = shallow(
			<OrganizerForm addOrganizer={ addOrganizer } onClose={ onClose } />,
		);
		const input = component.find( '[data-testid="organizer-form-input-phone"]' );
		const instance = component.instance();
		const spyIsValid = jest.spyOn( instance, 'isValid' );
		instance.fields = { 'organizer[phone]': input.dive().instance() };
		const inputField = instance.fields[ 'organizer[phone]' ];
		const spyOnChange = jest.spyOn( inputField, 'onChange' );

		expect( component.state( 'phone' ) ).toEqual( '' );
		inputField.onChange( 'not a phone number' );
		expect( component.state( 'phone' ) ).toEqual( 'not a phone number' );

		expect( spyOnChange ).toHaveBeenCalledWith( 'not a phone number' );
		expect( spyIsValid ).toHaveReturnedWith( false );
	} );

	test( 'should send a request to the wp-api to create a new Organizer on submit', () => {
		state.title = 'Organizer';

		const payload = {
			path: '/wp/v2/tribe_organizer',
			method: 'POST',
			data: {
				title: state.title,
				status: 'publish',
				meta: {
					_OrganizerEmail: state.email,
					_OrganizerPhone: state.phone,
					_OrganizerWebsite: state.website,
				},
			},
		};

		const component = shallow(
			<OrganizerForm addOrganizer={ addOrganizer } onClose={ onClose } />,
		);
		component.setState( state );
		const spyRequest = jest.spyOn( wp, 'apiRequest' );
		const button = component.find( '[data-testid="organizer-form-button-create"]' );
		button.simulate( 'click' );
		expect( spyRequest ).toHaveBeenCalledWith( payload );
	} );
} );
