// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import * as React from 'react';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import '@testing-library/jest-dom';
import { beforeEach, describe, expect, it, jest } from '@jest/globals';
import mockWpDataModule from '../_support/mockWpDataModule';
import TestProvider from '../_support/TestProvider';
import { METADATA_EVENT_ORGANIZER_ID } from '@tec/events/classy/constants';

const { mockSelect, mockUseDispatch } = mockWpDataModule();

// Mock the API functions.
jest.mock( '@tec/events/classy/api/organizers', () => ( {
	fetchOrganizers: jest.fn(),
	upsertOrganizer: jest.fn(),
} ) );

// Import the components after mocking the API to ensure the mocks are used.
import EventOrganizer from '@tec/events/classy/fields/EventOrganizer/EventOrganizer';
import { fetchOrganizers, upsertOrganizer } from '@tec/events/classy/api/organizers';

const mockOrganizers = [
	{
		id: 1,
		organizer: 'Sample Organizer 1',
		phone: '+1-555-0123',
		email: 'organizer1@example.com',
		website: 'https://organizer1.com',
		url: '',
	},
	{
		id: 2,
		organizer: 'Sample Organizer 2',
		phone: '+1-555-0124',
		email: 'organizer2@example.com',
		website: 'https://organizer2.com',
		url: '',
	},
];

describe( 'EventOrganizer', () => {
	let mockEditPost;
	let mockFetchOrganizers;
	let mockUpsertOrganizer;

	const setupMocks = ( meta = {} ) => {
		mockEditPost = jest.fn();
		mockFetchOrganizers = jest.fn();
		mockUpsertOrganizer = jest.fn();

		// Mock the API functions.
		( fetchOrganizers as jest.Mock ).mockImplementation( mockFetchOrganizers );
		( upsertOrganizer as jest.Mock ).mockImplementation( mockUpsertOrganizer );

		mockSelect.mockImplementation( ( store: string ): any => {
			if ( store === 'core/editor' ) {
				return {
					getEditedPostAttribute: ( attribute: string ): any => {
						return attribute === 'meta' ? meta : null;
					},
				};
			}

			// Throw an error for unknown stores to help identify what needs mocking.
			throw new Error( `Unknown store requested in mockSelect: ${ store }` );
		} );

		mockUseSelect.mockImplementation( ( callback: Function, deps?: any[] ): any => {
			// Call the callback with our mock select.
			return callback( mockSelect );
		} );

		mockUseDispatch.mockImplementation( ( store: string ): any => {
			if ( store === 'core/editor' ) {
				return {
					editPost: mockEditPost,
				};
			}

			// Throw an error for unknown stores to help identify what needs mocking.
			throw new Error( `Unknown store requested in mockUseDispatch: ${ store }` );
		} );

		// Set up API fetch mocks.
		if ( apiResponses.length > 0 ) {
			apiFetch.mockImplementation( ( { path, method, data } ) => {
				const response = apiResponses.shift();
				if ( response instanceof Error ) {
					return Promise.reject( response );
				}
				return Promise.resolve( response );
			} );
		} else {
			// Default implementation for fetching organizers.
			apiFetch.mockImplementation( ( { path, method, data } ) => {
				if ( path && path.includes( '/organizers' ) && ! method ) {
					// Extract page number from query string.
					const pageMatch = path.match( /page=(\d+)/ );
					const page = pageMatch ? parseInt( pageMatch[ 1 ] ) : 1;
					return Promise.resolve( createMockApiResponse( mockOrganizers, page ) );
				}

				// Mock organizer creation.
				if ( path && path.includes( '/organizers' ) && method === 'POST' ) {
					return Promise.resolve( {
						id: 5,
						link: 'https://example.com/organizer/new-organizer',
						title: { rendered: data.title },
						phone: data.phone || '',
						email: data.email || '',
						website: data.website || '',
					} );
				}

				// Mock organizer update.
				if ( path && path.match( /\/organizers\/\d+$/ ) && method === 'PUT' ) {
					const idMatch = path.match( /\/organizers\/(\d+)$/ );
					const id = idMatch ? parseInt( idMatch[ 1 ] ) : 0;
					return Promise.resolve( {
						id: id,
						link: `https://example.com/organizer/updated-${ id }`,
						title: { rendered: data.title },
						phone: data.phone || '',
						email: data.email || '',
						website: data.website || '',
					} );
				}

				return Promise.reject( new Error( 'Unknown API endpoint' ) );
			} );
		}
	};

	beforeAll( () => {
		jest.resetModules();
		jest.clearAllMocks();
	} );

	afterEach( () => {
		jest.clearAllMocks();
	} );

	afterAll( () => {
		jest.resetModules();
	} );

	it( 'should render the event organizer component with default title', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Check that the main elements are rendered.
		expect( screen.getByText( 'Event Organizer' ) ).toBeInTheDocument();

		// Should show the organizer selection dropdown when no organizers are selected.
		await waitFor( () => {
			expect( screen.getByText( 'Select organizer' ) ).toBeInTheDocument();
		} );

		expect( screen.getByText( 'or' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Create new organizer' ) ).toBeInTheDocument();
	} );

	it( 'should fetch and display available organizers in dropdown', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		let selectButton = null;
		// Wait for organizers to be fetched and loaded.
		await waitFor( () => {
			selectButton = screen.getByRole( 'combobox' );
			expect( selectButton ).toBeInTheDocument();
		} );

		// Open the dropdown.
		fireEvent.click( selectButton );

		// Check that organizers are displayed in the dropdown.
		await waitFor( () => {
			expect( screen.getByText( 'John Doe' ) ).toBeInTheDocument();
			expect( screen.getByText( 'Jane Smith' ) ).toBeInTheDocument();
			expect( screen.getByText( 'ACME Corporation' ) ).toBeInTheDocument();
			expect( screen.getByText( 'Tech Events Inc' ) ).toBeInTheDocument();
		} );
	} );

	it( 'should display existing organizers from meta', async () => {
		setupMocks( {
			[ METADATA_EVENT_ORGANIZER_ID ]: [ '1', '3' ],
		} );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for organizers to be fetched and displayed.
		await waitFor( () => {
			expect( screen.getByText( 'John Doe' ) ).toBeInTheDocument();
			expect( screen.getByText( 'ACME Corporation' ) ).toBeInTheDocument();
		} );

		// Check that contact details are displayed.
		expect( screen.getByText( '555-0100' ) ).toBeInTheDocument();
		expect( screen.getByText( 'john@example.com' ) ).toBeInTheDocument();
		expect( screen.getByText( 'https://johndoe.com' ) ).toBeInTheDocument();

		expect( screen.getByText( '555-0300' ) ).toBeInTheDocument();
		expect( screen.getByText( 'info@acme.com' ) ).toBeInTheDocument();
		expect( screen.getByText( 'https://acme.com' ) ).toBeInTheDocument();

		// Should show "Add another organizer" button.
		expect( screen.getByText( 'Add another organizer' ) ).toBeInTheDocument();
	} );

	it( 'should add an organizer when selected from dropdown', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for the dropdown to be ready.
		await waitFor( () => {
			expect( screen.getByRole( 'combobox' ) ).toBeInTheDocument();
		} );

		// Open the dropdown.
		const selectButton = screen.getByRole( 'combobox' );
		fireEvent.click( selectButton );

		// Select an organizer.
		await waitFor( () => {
			expect( screen.getByText( 'Jane Smith' ) ).toBeInTheDocument();
		} );

		fireEvent.click( screen.getByText( 'Jane Smith' ) );

		// Check that the organizer is added.
		await waitFor( () => {
			expect( screen.getByText( 'Jane Smith' ) ).toBeInTheDocument();
			expect( screen.getByText( '555-0200' ) ).toBeInTheDocument();
		} );

		// Check that editPost was called with the correct meta.
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_ORGANIZER_ID ]: [ 2 ] },
		} );
	} );

	it( 'should remove an organizer when delete button is clicked', async () => {
		setupMocks( {
			[ METADATA_EVENT_ORGANIZER_ID ]: [ '1', '2' ], // Must be strings as they're parsed as parseInt
		} );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for organizers to be displayed.
		await waitFor( () => {
			expect( screen.getByText( 'John Doe' ) ).toBeInTheDocument();
			expect( screen.getByText( 'Jane Smith' ) ).toBeInTheDocument();
		} );

		// Find and click the delete button for John Doe.
		// The card has a website button and action buttons. We need the action buttons.
		const johnDoeCard = screen.getByText( 'John Doe' ).closest( '.classy__linked-post-card' );
		// Get all buttons with the specific class for action buttons
		const actionButtons = johnDoeCard.querySelectorAll( '.classy-linked-post-card__action' );
		const deleteButton = actionButtons[ 1 ]; // Second action button is delete
		fireEvent.click( deleteButton );

		// Check that John Doe is removed but Jane Smith remains.
		await waitFor( () => {
			expect( screen.queryByText( 'John Doe' ) ).not.toBeInTheDocument();
			expect( screen.getByText( 'Jane Smith' ) ).toBeInTheDocument();
		} );

		// Check that editPost was called with updated meta.
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_ORGANIZER_ID ]: [ 2 ] },
		} );
	} );

	it( 'should show add another organizer button and handle click', async () => {
		setupMocks( {
			[ METADATA_EVENT_ORGANIZER_ID ]: [ '1' ],
		} );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for the organizer to be displayed.
		await waitFor( () => {
			expect( screen.getByText( 'John Doe' ) ).toBeInTheDocument();
		} );

		// Click "Add another organizer" button.
		const addButton = screen.getByText( 'Add another organizer' );
		fireEvent.click( addButton );

		// Should show the dropdown and create new organizer link.
		await waitFor( () => {
			expect( screen.getByRole( 'combobox' ) ).toBeInTheDocument();
			expect( screen.getByText( 'Create new organizer' ) ).toBeInTheDocument();
			expect( screen.getByText( 'Cancel' ) ).toBeInTheDocument();
		} );
	} );

	it( 'should cancel adding another organizer when cancel is clicked', async () => {
		setupMocks( {
			[ METADATA_EVENT_ORGANIZER_ID ]: [ '1' ],
		} );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for the organizer to be displayed.
		await waitFor( () => {
			expect( screen.getByText( 'John Doe' ) ).toBeInTheDocument();
		} );

		// Click "Add another organizer".
		fireEvent.click( screen.getByText( 'Add another organizer' ) );

		// Should show cancel button.
		await waitFor( () => {
			expect( screen.getByText( 'Cancel' ) ).toBeInTheDocument();
		} );

		// Click cancel.
		fireEvent.click( screen.getByText( 'Cancel' ) );

		// Should hide the dropdown and show "Add another organizer" again.
		await waitFor( () => {
			expect( screen.queryByRole( 'combobox' ) ).not.toBeInTheDocument();
			expect( screen.getByText( 'Add another organizer' ) ).toBeInTheDocument();
		} );
	} );

	it( 'should open create organizer modal when create link is clicked', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for the create link to be available.
		await waitFor( () => {
			expect( screen.getByText( 'Create new organizer' ) ).toBeInTheDocument();
		} );

		// Click the create new organizer link.
		fireEvent.click( screen.getByText( 'Create new organizer' ) );

		// Should open the modal.
		await waitFor( () => {
			expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();
			expect( screen.getByText( 'New Organizer' ) ).toBeInTheDocument();
		} );

		// Check for form fields in the modal.
		expect( screen.getByLabelText( 'Name' ) ).toBeInTheDocument();
		expect( screen.getByLabelText( 'Phone' ) ).toBeInTheDocument();
		expect( screen.getByLabelText( 'Website' ) ).toBeInTheDocument();
		expect( screen.getByLabelText( 'Email' ) ).toBeInTheDocument();
	} );

	it( 'should create a new organizer through the modal', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait and click create new organizer.
		await waitFor( () => {
			expect( screen.getByText( 'Create new organizer' ) ).toBeInTheDocument();
		} );

		fireEvent.click( screen.getByText( 'Create new organizer' ) );

		// Wait for modal to open.
		await waitFor( () => {
			expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();
		} );

		// Fill in the form.
		const nameInput = screen.getByLabelText( 'Name' );
		const phoneInput = screen.getByLabelText( 'Phone' );
		const emailInput = screen.getByLabelText( 'Email' );
		const websiteInput = screen.getByLabelText( 'Website' );

		fireEvent.change( nameInput, { target: { value: 'New Organizer' } } );
		fireEvent.change( phoneInput, { target: { value: '555-9999' } } );
		fireEvent.change( emailInput, { target: { value: 'new@example.com' } } );
		fireEvent.change( websiteInput, { target: { value: 'https://neworganizer.com' } } );

		// Click save button.
		const saveButton = screen.getByRole( 'button', { name: /Create Organizer/i } );
		fireEvent.click( saveButton );

		// Wait for the organizer to be created and added.
		await waitFor( () => {
			expect( screen.getByText( 'New Organizer' ) ).toBeInTheDocument();
			expect( screen.getByText( '555-9999' ) ).toBeInTheDocument();
		} );

		// Check that editPost was called.
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_ORGANIZER_ID ]: [ 5 ] },
		} );
	} );

	it( 'should open edit organizer modal when edit button is clicked', async () => {
		setupMocks( {
			[ METADATA_EVENT_ORGANIZER_ID ]: [ '1' ],
		} );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for organizer to be displayed.
		await waitFor( () => {
			expect( screen.getByText( 'John Doe' ) ).toBeInTheDocument();
		} );

		// Find and click the edit button.
		const organizerCard = screen.getByText( 'John Doe' ).closest( '.classy__linked-post-card' );
		const actionButtons = organizerCard.querySelectorAll( '.classy-linked-post-card__action' );
		const editButton = actionButtons[ 0 ]; // First action button is edit.
		fireEvent.click( editButton );

		// Should open the modal with existing data.
		await waitFor( () => {
			expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();
			// There are two "Update Organizer" texts - header and button
			const updateTexts = screen.getAllByText( 'Update Organizer' );
			expect( updateTexts.length ).toBeGreaterThan( 0 );
		} );

		// Check that form fields are populated with existing data.
		expect( screen.getByLabelText( 'Name' ) ).toHaveValue( 'John Doe' );
		expect( screen.getByLabelText( 'Phone' ) ).toHaveValue( '555-0100' );
		expect( screen.getByLabelText( 'Email' ) ).toHaveValue( 'john@example.com' );
		expect( screen.getByLabelText( 'Website' ) ).toHaveValue( 'https://johndoe.com' );
	} );

	it( 'should update an existing organizer through the modal', async () => {
		setupMocks( {
			[ METADATA_EVENT_ORGANIZER_ID ]: [ '1' ],
		} );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for organizer to be displayed.
		await waitFor( () => {
			expect( screen.getByText( 'John Doe' ) ).toBeInTheDocument();
		} );

		// Click edit button.
		const organizerCard = screen.getByText( 'John Doe' ).closest( '.classy__linked-post-card' );
		const actionButtons = organizerCard.querySelectorAll( '.classy-linked-post-card__action' );
		const editButton = actionButtons[ 0 ];
		fireEvent.click( editButton );

		// Wait for modal to open.
		await waitFor( () => {
			expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();
		} );

		// Update the form.
		const nameInput = screen.getByLabelText( 'Name' );
		const phoneInput = screen.getByLabelText( 'Phone' );

		fireEvent.change( nameInput, { target: { value: 'John Doe Updated' } } );
		fireEvent.change( phoneInput, { target: { value: '555-0111' } } );

		// Click save button - for update it's "Update Organizer"
		const saveButton = screen.getByRole( 'button', { name: /Update Organizer/i } );
		fireEvent.click( saveButton );

		// Wait for the organizer to be updated.
		await waitFor( () => {
			expect( screen.getByText( 'John Doe Updated' ) ).toBeInTheDocument();
			expect( screen.getByText( '555-0111' ) ).toBeInTheDocument();
		} );
	} );

	it( 'should close modal when cancel button is clicked', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Open create modal.
		await waitFor( () => {
			expect( screen.getByText( 'Create new organizer' ) ).toBeInTheDocument();
		} );

		fireEvent.click( screen.getByText( 'Create new organizer' ) );

		// Wait for modal to open.
		await waitFor( () => {
			expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();
		} );

		// Click cancel button.
		const cancelButton = screen.getByRole( 'button', { name: /Cancel/i } );
		fireEvent.click( cancelButton );

		// Modal should close.
		await waitFor( () => {
			expect( screen.queryByRole( 'dialog' ) ).not.toBeInTheDocument();
		} );
	} );

	it( 'should handle API fetch errors gracefully', async () => {
		const consoleErrorSpy = jest.spyOn( console, 'error' ).mockImplementation( () => {} );

		// Set up mock to return an error.
		setupMocks( {}, [ new Error( 'Network error' ) ] );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for the error to be handled.
		await waitFor( () => {
			expect( consoleErrorSpy ).toHaveBeenCalledWith(
				expect.stringContaining( 'Organizer fetch request failed' )
			);
		} );

		// Component should still render and be functional.
		expect( screen.getByText( 'Event Organizer' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Create new organizer' ) ).toBeInTheDocument();

		consoleErrorSpy.mockRestore();
	} );

	it( 'should handle organizer creation errors gracefully', async () => {
		const consoleErrorSpy = jest.spyOn( console, 'error' ).mockImplementation( () => {} );

		// Setup mocks with successful fetch but failed creation.
		setupMocks( {}, [
			createMockApiResponse( mockOrganizers, 1 ), // First call for initial fetch
			new Error( 'Creation failed' ), // Second call for POST will fail
		] );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Open create modal.
		await waitFor( () => {
			expect( screen.getByText( 'Create new organizer' ) ).toBeInTheDocument();
		} );

		fireEvent.click( screen.getByText( 'Create new organizer' ) );

		// Wait for modal.
		await waitFor( () => {
			expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();
		} );

		// Fill form and save.
		fireEvent.change( screen.getByLabelText( 'Name' ), { target: { value: 'Test Organizer' } } );
		fireEvent.click( screen.getByRole( 'button', { name: /Create Organizer/i } ) );

		// Wait for error to be handled.
		await waitFor( () => {
			expect( consoleErrorSpy ).toHaveBeenCalledWith(
				expect.stringContaining( 'Organizer upsert request failed' )
			);
		} );

		// Modal should close after error.
		await waitFor( () => {
			expect( screen.queryByRole( 'dialog' ) ).not.toBeInTheDocument();
		} );

		consoleErrorSpy.mockRestore();
	} );

	it( 'should filter out already selected organizers from dropdown', async () => {
		setupMocks( {
			[ METADATA_EVENT_ORGANIZER_ID ]: [ '1', '3' ],
		} );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for organizers to be displayed.
		await waitFor( () => {
			expect( screen.getByText( 'John Doe' ) ).toBeInTheDocument();
			expect( screen.getByText( 'ACME Corporation' ) ).toBeInTheDocument();
		} );

		// Click "Add another organizer".
		fireEvent.click( screen.getByText( 'Add another organizer' ) );

		// Open the dropdown.
		await waitFor( () => {
			expect( screen.getByRole( 'combobox' ) ).toBeInTheDocument();
		} );

		const selectButton = screen.getByRole( 'combobox' );
		fireEvent.click( selectButton );

		// Check that already selected organizers are not in the dropdown.
		await waitFor( () => {
			expect( screen.queryByText( 'Jane Smith' ) ).toBeInTheDocument();
			expect( screen.queryByText( 'Tech Events Inc' ) ).toBeInTheDocument();
			// John Doe and ACME Corporation should not appear in dropdown as they're already selected.
			const dropdown = screen.getByRole( 'listbox' );
			expect( within( dropdown ).queryByText( 'John Doe' ) ).not.toBeInTheDocument();
			expect( within( dropdown ).queryByText( 'ACME Corporation' ) ).not.toBeInTheDocument();
		} );
	} );

	it( 'should handle multiple organizers selection and removal', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Select first organizer.
		await waitFor( () => {
			expect( screen.getByRole( 'combobox' ) ).toBeInTheDocument();
		} );

		let selectButton = screen.getByRole( 'combobox' );
		fireEvent.click( selectButton );

		await waitFor( () => {
			expect( screen.getByText( 'John Doe' ) ).toBeInTheDocument();
		} );

		fireEvent.click( screen.getByText( 'John Doe' ) );

		// Verify first organizer is added.
		await waitFor( () => {
			const cards = screen.getAllByText( 'John Doe' );
			expect( cards.length ).toBeGreaterThan( 0 );
		} );

		// Add another organizer.
		fireEvent.click( screen.getByText( 'Add another organizer' ) );

		await waitFor( () => {
			expect( screen.getByRole( 'combobox' ) ).toBeInTheDocument();
		} );

		selectButton = screen.getByRole( 'combobox' );
		fireEvent.click( selectButton );

		await waitFor( () => {
			expect( screen.getByText( 'Jane Smith' ) ).toBeInTheDocument();
		} );

		fireEvent.click( screen.getByText( 'Jane Smith' ) );

		// Verify both organizers are displayed.
		await waitFor( () => {
			expect( screen.getByText( '555-0100' ) ).toBeInTheDocument(); // John's phone.
			expect( screen.getByText( '555-0200' ) ).toBeInTheDocument(); // Jane's phone.
		} );

		// Check that editPost was called with both organizer IDs.
		expect( mockEditPost ).toHaveBeenLastCalledWith( {
			meta: { [ METADATA_EVENT_ORGANIZER_ID ]: [ 1, 2 ] },
		} );

		// Remove first organizer.
		const johnCard = screen.getByText( '555-0100' ).closest( '.classy__linked-post-card' );
		const actionButtons = johnCard.querySelectorAll( '.classy-linked-post-card__action' );
		const deleteButton = actionButtons[ 1 ];
		fireEvent.click( deleteButton );

		// Verify only Jane remains.
		await waitFor( () => {
			expect( screen.queryByText( '555-0100' ) ).not.toBeInTheDocument();
			expect( screen.getByText( '555-0200' ) ).toBeInTheDocument();
		} );

		expect( mockEditPost ).toHaveBeenLastCalledWith( {
			meta: { [ METADATA_EVENT_ORGANIZER_ID ]: [ 2 ] },
		} );
	} );

	it( 'should handle pagination when fetching many organizers', async () => {
		// Create a large set of organizers to trigger pagination.
		const manyOrganizers = Array.from( { length: 25 }, ( _, i ) => ( {
			id: i + 1,
			url: `https://example.com/organizer/organizer-${ i + 1 }`,
			organizer: `Organizer ${ i + 1 }`,
			phone: `555-${ String( i + 1 ).padStart( 4, '0' ) }`,
			email: `organizer${ i + 1 }@example.com`,
			website: `https://organizer${ i + 1 }.com`,
		} ) );

		// Setup mocks will override the apiFetch implementation
		setupMocks( {}, [
			createMockApiResponse( manyOrganizers, 1, 10 ), // First page
			createMockApiResponse( manyOrganizers, 2, 10 ), // Second page
			createMockApiResponse( manyOrganizers, 3, 10 ), // Third page
		] );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Open dropdown to trigger fetching.
		await waitFor( () => {
			expect( screen.getByRole( 'combobox' ) ).toBeInTheDocument();
		} );

		const selectButton = screen.getByRole( 'combobox' );
		fireEvent.click( selectButton );

		// Should fetch multiple pages and show organizers.
		await waitFor(
			() => {
				// Check for organizers from different pages.
				expect( screen.getByText( 'Organizer 1' ) ).toBeInTheDocument();
				expect( screen.getByText( 'Organizer 10' ) ).toBeInTheDocument();
			},
			{ timeout: 3000 }
		);
	} );

	it( 'should maintain organizer order as selected by user', async () => {
		setupMocks( {
			[ METADATA_EVENT_ORGANIZER_ID ]: [ '3', '1', '2' ],
		} );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for organizers to be displayed.
		await waitFor( () => {
			const phoneElements = screen.getAllByText( /555-0[1-3]00/ );
			expect( phoneElements ).toHaveLength( 3 );
		} );

		// Get all organizer cards in order.
		const cards = document.querySelectorAll( '.classy__linked-post-card' );

		// Verify order matches the meta order (3, 1, 2).
		expect( cards[ 0 ] ).toHaveTextContent( 'ACME Corporation' );
		expect( cards[ 0 ] ).toHaveTextContent( '555-0300' );

		expect( cards[ 1 ] ).toHaveTextContent( 'John Doe' );
		expect( cards[ 1 ] ).toHaveTextContent( '555-0100' );

		expect( cards[ 2 ] ).toHaveTextContent( 'Jane Smith' );
		expect( cards[ 2 ] ).toHaveTextContent( '555-0200' );
	} );

	it( 'should handle empty organizer fields gracefully', async () => {
		const minimalOrganizers = [
			{
				id: 10,
				url: 'https://example.com/organizer/minimal',
				organizer: 'Minimal Organizer',
				phone: '',
				email: '',
				website: '',
			},
		];

		setupMocks(
			{
				[ METADATA_EVENT_ORGANIZER_ID ]: [ '10' ],
			},
			[ createMockApiResponse( minimalOrganizers, 1 ) ]
		);

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for organizer to be displayed.
		await waitFor( () => {
			expect( screen.getByText( 'Minimal Organizer' ) ).toBeInTheDocument();
		} );

		// Should not display empty fields.
		expect( screen.queryByText( '555-' ) ).not.toBeInTheDocument();
		expect( screen.queryByText( '@example.com' ) ).not.toBeInTheDocument();
		expect( screen.queryByText( 'https://' ) ).not.toBeInTheDocument();
	} );
	it( 'should handle special characters in organizer names', async () => {
		const specialOrganizers = [
			{
				id: 20,
				url: 'https://example.com/organizer/special',
				organizer: 'O&apos;Brien &amp; Associates &lt;Special&gt;',
				phone: '555-2000',
				email: 'info@obrien.com',
				website: 'https://obrien.com',
			},
		];

		setupMocks(
			{
				[ METADATA_EVENT_ORGANIZER_ID ]: [ '20' ],
			},
			[ createMockApiResponse( specialOrganizers, 1 ) ]
		);

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for organizer to be displayed with decoded entities.
		await waitFor( () => {
			expect( screen.getByText( "O'Brien & Associates <Special>" ) ).toBeInTheDocument();
		} );
	} );

	it( 'should automatically select newly created organizer', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Create a new organizer.
		await waitFor( () => {
			expect( screen.getByText( 'Create new organizer' ) ).toBeInTheDocument();
		} );

		fireEvent.click( screen.getByText( 'Create new organizer' ) );

		await waitFor( () => {
			expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();
		} );

		// Fill in the form.
		fireEvent.change( screen.getByLabelText( 'Name' ), { target: { value: 'Auto Selected Org' } } );
		fireEvent.change( screen.getByLabelText( 'Phone' ), { target: { value: '555-AUTO' } } );

		// Save the organizer.
		fireEvent.click( screen.getByRole( 'button', { name: /Create Organizer/i } ) );

		// Verify the organizer is automatically added to the selection.
		await waitFor( () => {
			expect( screen.getByText( 'Auto Selected Org' ) ).toBeInTheDocument();
			expect( screen.getByText( '555-AUTO' ) ).toBeInTheDocument();
		} );

		// Verify editPost was called with the new organizer ID.
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_ORGANIZER_ID ]: [ 5 ] },
		} );
	} );

	it( 'should close dropdown after selecting an organizer when adding first', async () => {
		setupMocks();

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Open dropdown.
		await waitFor( () => {
			expect( screen.getByRole( 'combobox' ) ).toBeInTheDocument();
		} );

		const selectButton = screen.getByRole( 'combobox' );
		fireEvent.click( selectButton );

		// Select an organizer.
		await waitFor( () => {
			expect( screen.getByText( 'John Doe' ) ).toBeInTheDocument();
		} );

		fireEvent.click( screen.getByText( 'John Doe' ) );

		// Verify dropdown is closed and "Add another organizer" button is shown.
		await waitFor( () => {
			expect( screen.queryByRole( 'combobox' ) ).not.toBeInTheDocument();
			expect( screen.getByText( 'Add another organizer' ) ).toBeInTheDocument();
		} );
	} );

	it( 'should not close dropdown after selecting when adding additional organizers', async () => {
		setupMocks( {
			[ METADATA_EVENT_ORGANIZER_ID ]: [ '1' ],
		} );

		render(
			<TestProvider>
				<EventOrganizer title="Event Organizer" />
			</TestProvider>
		);

		// Wait for initial organizer.
		await waitFor( () => {
			expect( screen.getByText( 'John Doe' ) ).toBeInTheDocument();
		} );

		// Click "Add another organizer".
		fireEvent.click( screen.getByText( 'Add another organizer' ) );

		// Open dropdown.
		await waitFor( () => {
			expect( screen.getByRole( 'combobox' ) ).toBeInTheDocument();
		} );

		const selectButton = screen.getByRole( 'combobox' );
		fireEvent.click( selectButton );

		// Select another organizer.
		await waitFor( () => {
			expect( screen.getByText( 'Jane Smith' ) ).toBeInTheDocument();
		} );

		fireEvent.click( screen.getByText( 'Jane Smith' ) );

		// Verify Jane is added but dropdown remains available.
		await waitFor( () => {
			expect( screen.getByText( '555-0200' ) ).toBeInTheDocument();
			// The dropdown should be closed after selection.
			expect( screen.queryByRole( 'combobox' ) ).not.toBeInTheDocument();
			expect( screen.getByText( 'Add another organizer' ) ).toBeInTheDocument();
		} );
	} );
} );
