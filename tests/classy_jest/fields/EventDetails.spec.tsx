// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import * as React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import { describe, expect, it, jest } from '@jest/globals';
import mockWpDataModule from '../_support/mockWpDataModule';

import TestProvider from '../_support/TestProvider';

const { mockSelect, mockUseSelect, mockUseDispatch } = mockWpDataModule();

// Mock TinyMceEditor component.
jest.mock( '@tec/common/classy/components', () => ( {
	TinyMceEditor: jest.fn( ( { content, onChange, id } ) => (
		<textarea
			data-testid={ id }
			value={ content }
			onChange={ ( e ) => onChange( e.target.value ) }
			aria-label="Description editor"
		/>
	) ),
} ) );

// Mock PostFeaturedImage component.
jest.mock( '@wordpress/editor', () => ( {
	PostFeaturedImage: jest.fn( () => <div data-testid="featured-image-component">Featured Image Component</div> ),
} ) );

// Mock isValidUrl function.
jest.mock( '@tec/common/classy/functions', () => ( {
	isValidUrl: jest.fn( ( url: string ) => {
		if ( ! url ) return true; // Empty is valid.
		try {
			// Basic URL validation.
			const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
			return urlPattern.test( url ) || url.startsWith( 'www.' );
		} catch {
			return false;
		}
	} ),
} ) );

import EventDetails from '../../../src/resources/packages/classy/fields/EventDetails/EventDetails';
import { METADATA_EVENT_URL } from '@tec/events/classy/constants';
import { isValidUrl } from '@tec/common/classy/functions';

describe( 'EventDetails', () => {
	let mockEditPost;

	const setupMocks = ( postContent = '', meta = {} ) => {
		mockEditPost = jest.fn();

		mockSelect.mockImplementation( ( store: string ): any => {
			if ( store === 'core/editor' ) {
				return {
					getEditedPostContent: () => postContent,
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
	};

	beforeAll( () => {
		jest.resetModules();
		jest.clearAllMocks();
	} );

	afterAll( () => {
		jest.resetModules();
	} );

	it( 'should render the event details component with all sections', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		// Check that the main title is rendered.
		expect( screen.getByText( 'Event details' ) ).toBeInTheDocument();

		// Check that all sections are rendered.
		expect( screen.getByText( 'Description' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Featured Image' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Event website' ) ).toBeInTheDocument();

		// Check helper text.
		expect( screen.getByText( 'Describe your event' ) ).toBeInTheDocument();
		expect( screen.getByText( 'We recommend a 16:9 aspect ratio for featured images.' ) ).toBeInTheDocument();

		// Check the description editor is rendered.
		expect( screen.getByTestId( 'classy-event-details-description-editor' ) ).toBeInTheDocument();

		// Check the featured image component is rendered.
		expect( screen.getByTestId( 'featured-image-component' ) ).toBeInTheDocument();

		// Check the URL input is rendered.
		expect( screen.getByPlaceholderText( 'www.example.com' ) ).toBeInTheDocument();
	} );

	it( 'should display existing post content in description editor', () => {
		const existingContent = '<p>This is an existing event description.</p>';
		setupMocks( existingContent );

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const descriptionEditor = screen.getByTestId( 'classy-event-details-description-editor' );
		expect( descriptionEditor ).toHaveValue( existingContent );
	} );

	it( 'should update post content when description changes', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const descriptionEditor = screen.getByTestId( 'classy-event-details-description-editor' );
		const newContent = 'Updated event description';

		fireEvent.change( descriptionEditor, { target: { value: newContent } } );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			content: newContent,
		} );
	} );

	it( 'should handle empty description content', () => {
		setupMocks( 'Initial content' );

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const descriptionEditor = screen.getByTestId( 'classy-event-details-description-editor' );

		// Clear the content.
		fireEvent.change( descriptionEditor, { target: { value: '' } } );

		expect( mockEditPost ).toHaveBeenCalledWith( {
			content: '',
		} );
	} );

	it( 'should display existing event URL from meta', () => {
		const eventUrl = 'www.example-event.com';
		setupMocks( '', { [ METADATA_EVENT_URL ]: eventUrl } );

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const urlInput = screen.getByPlaceholderText( 'www.example.com' );
		expect( urlInput ).toHaveValue( eventUrl );
	} );

	it( 'should update event URL when valid URL is entered', () => {
		setupMocks();
		( isValidUrl as jest.Mock ).mockImplementation( ( url ) => url.includes( 'example.com' ) );

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const urlInput = screen.getByPlaceholderText( 'www.example.com' );
		const validUrl = 'www.example.com';

		fireEvent.change( urlInput, { target: { value: validUrl } } );

		expect( isValidUrl ).toHaveBeenCalledWith( validUrl );
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_URL ]: validUrl },
		} );
	} );

	it( 'should show error message for invalid URL', () => {
		setupMocks();
		( isValidUrl as jest.Mock ).mockReturnValue( false );

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const urlInput = screen.getByPlaceholderText( 'www.example.com' );
		const invalidUrl = 'not-a-url';

		fireEvent.change( urlInput, { target: { value: invalidUrl } } );

		// Should show error message.
		expect( screen.getByText( 'Must be a valid URL' ) ).toBeInTheDocument();

		// Should not update meta.
		expect( mockEditPost ).not.toHaveBeenCalled();

		// Input should have invalid class.
		const inputContainer = urlInput.closest( '.classy-field__control' );
		expect( inputContainer ).toHaveClass( 'classy-field__control--invalid' );
	} );

	it( 'should clear URL validation error when valid URL is entered', () => {
		setupMocks();
		const mockIsValidUrl = isValidUrl as jest.Mock;

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const urlInput = screen.getByPlaceholderText( 'www.example.com' );

		// First enter invalid URL.
		mockIsValidUrl.mockReturnValue( false );
		fireEvent.change( urlInput, { target: { value: 'invalid' } } );

		// Error should be shown.
		expect( screen.getByText( 'Must be a valid URL' ) ).toBeInTheDocument();

		// Now enter valid URL.
		mockIsValidUrl.mockReturnValue( true );
		fireEvent.change( urlInput, { target: { value: 'www.valid-url.com' } } );

		// Error should be gone.
		expect( screen.queryByText( 'Must be a valid URL' ) ).not.toBeInTheDocument();

		// Should update meta with valid URL.
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_URL ]: 'www.valid-url.com' },
		} );
	} );

	it( 'should handle empty URL as valid', () => {
		setupMocks( '', { [ METADATA_EVENT_URL ]: 'www.existing.com' } );
		( isValidUrl as jest.Mock ).mockImplementation( ( url ) => ! url || url.includes( '.' ) );

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const urlInput = screen.getByPlaceholderText( 'www.example.com' );

		// Clear the URL.
		fireEvent.change( urlInput, { target: { value: '' } } );

		// Should not show error.
		expect( screen.queryByText( 'Must be a valid URL' ) ).not.toBeInTheDocument();

		// Should update meta with empty value.
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_URL ]: '' },
		} );
	} );

	it( 'should accept URLs with different formats', () => {
		setupMocks();
		( isValidUrl as jest.Mock ).mockReturnValue( true );

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const urlInput = screen.getByPlaceholderText( 'www.example.com' );
		const testUrls = [
			'https://example.com',
			'http://example.com',
			'www.example.com',
			'example.com/path',
			'subdomain.example.com',
		];

		testUrls.forEach( ( url ) => {
			fireEvent.change( urlInput, { target: { value: url } } );
			expect( isValidUrl ).toHaveBeenCalledWith( url );
			expect( mockEditPost ).toHaveBeenCalledWith( {
				meta: { [ METADATA_EVENT_URL ]: url },
			} );
		} );
	} );

	it( 'should update content and URL independently', () => {
		setupMocks();
		( isValidUrl as jest.Mock ).mockReturnValue( true );

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const descriptionEditor = screen.getByTestId( 'classy-event-details-description-editor' );
		const urlInput = screen.getByPlaceholderText( 'www.example.com' );

		// Update description.
		fireEvent.change( descriptionEditor, { target: { value: 'New description' } } );
		expect( mockEditPost ).toHaveBeenCalledWith( {
			content: 'New description',
		} );

		// Update URL.
		fireEvent.change( urlInput, { target: { value: 'www.new-url.com' } } );
		expect( mockEditPost ).toHaveBeenCalledWith( {
			meta: { [ METADATA_EVENT_URL ]: 'www.new-url.com' },
		} );

		// Verify both were called independently.
		expect( mockEditPost ).toHaveBeenCalledTimes( 2 );
	} );

	it( 'should sync state with prop updates for post content', () => {
		const { rerender } = render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		// Initial state.
		const descriptionEditor = screen.getByTestId( 'classy-event-details-description-editor' );
		expect( descriptionEditor ).toHaveValue( '' );

		// Update mocks to return new content.
		setupMocks( 'Updated content from props' );

		// Re-render component.
		rerender(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		// Should reflect new content.
		expect( descriptionEditor ).toHaveValue( 'Updated content from props' );
	} );

	it( 'should sync state with prop updates for event URL', () => {
		const { rerender } = render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		// Initial state.
		const urlInput = screen.getByPlaceholderText( 'www.example.com' );
		expect( urlInput ).toHaveValue( '' );

		// Update mocks to return new URL.
		setupMocks( '', { [ METADATA_EVENT_URL ]: 'www.updated-url.com' } );

		// Re-render component.
		rerender(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		// Should reflect new URL.
		expect( urlInput ).toHaveValue( 'www.updated-url.com' );
	} );

	it( 'should maintain invalid state when URL is invalid across re-renders', () => {
		setupMocks();
		( isValidUrl as jest.Mock ).mockReturnValue( false );

		const { rerender } = render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const urlInput = screen.getByPlaceholderText( 'www.example.com' );

		// Enter invalid URL.
		fireEvent.change( urlInput, { target: { value: 'invalid-url' } } );

		// Should show error.
		expect( screen.getByText( 'Must be a valid URL' ) ).toBeInTheDocument();

		// Re-render without changing mocks.
		rerender(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		// Error should still be visible.
		expect( screen.getByText( 'Must be a valid URL' ) ).toBeInTheDocument();
	} );

	it( 'should handle null or undefined meta gracefully', () => {
		// Test with null meta.
		setupMocks( '', null );

		const { rerender } = render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const urlInput = screen.getByPlaceholderText( 'www.example.com' );
		expect( urlInput ).toHaveValue( '' );

		// Test with undefined meta.
		setupMocks( '', undefined );

		rerender(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		expect( urlInput ).toHaveValue( '' );
	} );

	it( 'should render with custom title prop', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDetails title="Custom Event Details Title" />
			</TestProvider>
		);

		expect( screen.getByText( 'Custom Event Details Title' ) ).toBeInTheDocument();
	} );

	it( 'should handle rapid consecutive changes to description', () => {
		setupMocks();

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const descriptionEditor = screen.getByTestId( 'classy-event-details-description-editor' );

		// Simulate rapid typing.
		const changes = [ 'H', 'He', 'Hel', 'Hell', 'Hello' ];

		changes.forEach( ( value ) => {
			fireEvent.change( descriptionEditor, { target: { value } } );
		} );

		// Should have called editPost for each change.
		expect( mockEditPost ).toHaveBeenCalledTimes( 5 );
		expect( mockEditPost ).toHaveBeenLastCalledWith( {
			content: 'Hello',
		} );
	} );

	it( 'should handle rapid consecutive changes to URL', () => {
		setupMocks();
		( isValidUrl as jest.Mock ).mockReturnValue( true );

		render(
			<TestProvider>
				<EventDetails title="Event details" />
			</TestProvider>
		);

		const urlInput = screen.getByPlaceholderText( 'www.example.com' );

		// Simulate rapid typing.
		const changes = [ 'w', 'ww', 'www', 'www.', 'www.e', 'www.ex', 'www.example.com' ];

		changes.forEach( ( value ) => {
			fireEvent.change( urlInput, { target: { value } } );
		} );

		// Should validate each change (may be called more times due to re-renders).
		const callCount = ( isValidUrl as jest.Mock ).mock.calls.length;
		expect( callCount ).toBeGreaterThanOrEqual( 7 );

		// Should update meta only for valid URLs.
		const validCallsCount = mockEditPost.mock.calls.filter(
			( call ) => call[ 0 ].meta?.[ METADATA_EVENT_URL ] === 'www.example.com'
		).length;
		expect( validCallsCount ).toBeGreaterThan( 0 );
	} );
} );
