/**
 * External dependencies
 */
import React from 'react';
import renderer from 'react-test-renderer';

/**
 * WordPress dependencies - Mock them
 */
jest.mock( '@wordpress/components', () => ( {
	Spinner: () => <div>Spinner</div>,
	Placeholder: ( { children } ) => <div>{ children }</div>,
} ) );

jest.mock( '@wordpress/i18n', () => ( {
	__: ( text ) => text,
} ) );

/**
 * Internal dependencies - Mock Input component
 */
jest.mock( '@moderntribe/events/elements', () => ( {
	Input: ( props ) => <input { ...props } />,
} ) );

/**
 * Internal dependencies
 */
import OrganizerForm from '../element';

describe( 'OrganizerForm', () => {
	const addOrganizer = jest.fn();
	const onClose = jest.fn();

	beforeEach( () => {
		addOrganizer.mockClear();
		onClose.mockClear();
	} );

	test( 'should render the form', () => {
		const component = renderer.create(
			<OrganizerForm addOrganizer={ addOrganizer } onClose={ onClose } />
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	test( 'should render with custom post type', () => {
		const component = renderer.create(
			<OrganizerForm
				addOrganizer={ addOrganizer }
				onClose={ onClose }
				postType="custom_organizer"
			/>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	test( 'should call wp.apiRequest when creating organizer', () => {
		// Mock wp.apiRequest
		const mockRequest = {
			done: jest.fn( function() {
				return this;
			} ),
			fail: jest.fn( function() {
				return this;
			} ),
		};
		const apiRequestSpy = jest.spyOn( wp, 'apiRequest' ).mockReturnValue( mockRequest );

		const component = renderer.create(
			<OrganizerForm addOrganizer={ addOrganizer } onClose={ onClose } />
		);

		// Get the component instance to access internal methods
		const instance = component.root.instance;

		// Simulate form submission with some data
		instance.setState( {
			title: 'Test Organizer',
			phone: '555-1234',
			website: 'https://example.com',
			email: 'test@example.com',
		} );

		// Mock isValid to return true
		instance.isValid = jest.fn( () => true );

		// Call onSubmit
		instance.onSubmit();

		// Verify API was called with correct payload
		expect( apiRequestSpy ).toHaveBeenCalledWith( {
			path: '/wp/v2/tribe_organizer',
			method: 'POST',
			data: {
				title: 'Test Organizer',
				status: 'publish',
				meta: {
					_OrganizerEmail: 'test@example.com',
					_OrganizerPhone: '555-1234',
					_OrganizerWebsite: 'https://example.com',
				},
			},
		} );

		// Cleanup
		apiRequestSpy.mockRestore();
	} );
} );
