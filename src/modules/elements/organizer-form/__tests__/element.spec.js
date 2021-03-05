/**
 * External dependencies
 */
import React from 'react';
import { shallow } from 'enzyme';

/**
 * Internal dependencies
 */
import OrganizerForm from '../element';

describe( 'OrganizerForm', () => {
	test( 'should send a request to the wp-api to create a new Organizer on submit', () => {
		const addOrganizer = jest.fn();
		const onClose = jest.fn();

		const state = {
			title: 'Organizer',
			email: '',
			phone: '',
			website: '',
		};

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
			<OrganizerForm addOrganizer={ addOrganizer } onClose={ onClose } />
		);
		component.setState( state );
		// const spy = jest.spyOn( component.instance(), 'updateOrganizer' );
		const spyRequest = jest.spyOn( wp, 'apiRequest' );
		// const spyConsole = jest.spyOn( console, 'warning' );
		// component.instance().forceUpdate();
		const button = component.find( '.button-secondary' );
		button.simulate( 'click' );
		// expect( spy ).not.toHaveReturned();
		expect( spyRequest ).toHaveBeenCalledWith( payload );
		// expect( spyConsole ).toHaveBeenCalled();
		// expect( updateOrganizer ).toHaveBeenCalledTimes( 1 );
		// expect( addOrganizer ).toHaveBeenCalledTimes( 1 );
	} );
} );
