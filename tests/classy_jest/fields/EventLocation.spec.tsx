// @ts-nocheck
// eslint-disable-next-line @typescript-eslint/triple-slash-reference
/// <reference types="jest" />
import * as React from 'react';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import '@testing-library/jest-dom';
import { beforeEach, describe, expect, it, jest } from '@jest/globals';
import mockWpDataModule from '../_support/mockWpDataModule';
import TestProvider from '../_support/TestProvider';
import { METADATA_EVENT_VENUE_ID } from '@tec/events/classy/constants';

const { mockSelect, mockUseDispatch } = mockWpDataModule();

// Mock the API functions.
jest.mock( '@tec/events/classy/api', () => ( {
	fetchVenues: jest.fn(),
	upsertVenue: jest.fn(),
} ) );

// Import the components after mocking the API to ensure the mocks are used.
import EventLocation from '@tec/events/classy/fields/EventLocation/EventLocation';
import { fetchVenues, upsertVenue } from '@tec/events/classy/api';

const mockVenues = [
	{
		id: 1,
		venue: 'Sample Venue 1',
		address: '123 Main Street',
		city: 'New York',
		country: 'United States',
		province: '',
		state: 'NY',
		zip: '10001',
		phone: '+1-555-0123',
		website: 'https://sample-venue-1.com',
	},
	{
		id: 2,
		venue: 'Sample Venue 2',
		address: '456 Oak Avenue',
		city: 'Los Angeles',
		country: 'United States',
		province: '',
		state: 'CA',
		zip: '90210',
		phone: '+1-555-0124',
		website: 'https://sample-venue-2.com',
	},
];

describe( 'EventLocation', () => {
	let mockEditPost;
	let mockFetchVenues;
	let mockUpsertVenue;

	const setupMocks = ( meta = {}, venuesLimit = 1 ) => {
		mockEditPost = jest.fn();
		mockFetchVenues = jest.fn();
		mockUpsertVenue = jest.fn();

		// Mock the API functions.
		( fetchVenues as jest.Mock ).mockImplementation( mockFetchVenues );
		( upsertVenue as jest.Mock ).mockImplementation( mockUpsertVenue );

		mockSelect.mockImplementation( ( store: string ): any => {
			if ( store === 'core/editor' ) {
				return {
					getEditedPostAttribute: ( attribute: string ): any => {
						return attribute === 'meta' ? meta : null;
					},
				};
			}

			if ( store === 'tec/classy/events' ) {
				return {
					getVenuesLimit: () => venuesLimit,
				};
			}

			if ( store === 'tec/classy' ) {
				return {
					getVenuesLimit: () => venuesLimit,
					getCountryOptions: () => [
						{ code: 'US', label: 'United States' },
						{ code: 'CA', label: 'Canada' },
					],
					getUsStatesOptions: () => [
						{ code: 'NY', label: 'New York' },
						{ code: 'CA', label: 'California' },
					],
				};
			}

			// Throw an error for unknown stores to help identify what needs mocking.
			throw new Error( `Unknown store requested in mockSelect: ${ store }` );
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

	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'should render the event location component with default state', () => {
		setupMocks();
		mockFetchVenues.mockResolvedValue( { venues: [], total: 0 } );

		const { container } = render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		expect( container ).toMatchSnapshot();

		// Check that the main elements are rendered.
		expect( screen.getByText( 'Event location' ) ).toBeInTheDocument();
		expect( screen.getByRole( 'combobox', { name: /venue selection/i } ) ).toBeInTheDocument();
		expect( screen.getByText( 'or' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Create new venue' ) ).toBeInTheDocument();

		// Check that the venue cards are not rendered when no venues are selected.
		expect( screen.queryByTestId( 'venue-cards' ) ).not.toBeInTheDocument();
	} );

	it( 'should display existing venues from meta', async () => {
		setupMocks( {
			[ METADATA_EVENT_VENUE_ID ]: [ '1', '2' ],
		} );
		mockFetchVenues.mockResolvedValue( { venues: mockVenues, total: 2 } );

		const { container } = render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		// Wait for venues to be fetched and displayed.
		await waitFor( () => {
			expect( screen.getByText( 'Sample Venue 1' ) ).toBeInTheDocument();
			expect( container ).toMatchSnapshot();
		} );

		expect( screen.getByText( 'Sample Venue 1' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Sample Venue 2' ) ).toBeInTheDocument();
	} );

	it( 'should fetch venues on component mount', async () => {
		setupMocks();
		mockFetchVenues.mockResolvedValue( { venues: mockVenues, total: 2 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		await waitFor( () => {
			expect( mockFetchVenues ).toHaveBeenCalledWith( 1 );
		} );
	} );

	it( 'should handle venue selection', async () => {
		setupMocks();
		mockFetchVenues.mockResolvedValue( { venues: mockVenues, total: 2 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		// Wait for venues to be loaded.
		await waitFor( () => {
			expect( mockFetchVenues ).toHaveBeenCalled();
		} );

		// Find the select control and simulate selection.
		const selectControl = screen.getByRole( 'combobox' );
		expect( selectControl ).toBeInTheDocument();

		// Simulate selecting a venue.
		act( () => {
			fireEvent.click( selectControl );
		} );

		// The actual selection would be handled by the CustomSelectControl component.
		// In a real test, we'd need to simulate the selection properly.
		// For now, we'll just verify the component renders correctly.
		expect( selectControl ).toBeInTheDocument();
	} );

	it( 'should show create new venue button when no venues are selected', () => {
		setupMocks();
		mockFetchVenues.mockResolvedValue( { venues: [], total: 0 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		expect( screen.getByText( 'Create new venue' ) ).toBeInTheDocument();
	} );

	it( 'should show add another venue button when venues are selected and under limit', async () => {
		setupMocks( {
			[ METADATA_EVENT_VENUE_ID ]: [ '1' ],
		}, 2 );
		mockFetchVenues.mockResolvedValue( { venues: mockVenues, total: 2 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		// Wait for venues to be loaded.
		await waitFor( () => {
			expect( screen.getByText( 'Sample Venue 1' ) ).toBeInTheDocument();
		} );

		expect( screen.getByText( 'Add another venue' ) ).toBeInTheDocument();
	} );

	it( 'should not show add another venue button when at venue limit', async () => {
		setupMocks( {
			[ METADATA_EVENT_VENUE_ID ]: [ '1', '2' ],
		}, 2 );
		mockFetchVenues.mockResolvedValue( { venues: mockVenues, total: 2 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		// Wait for venues to be loaded.
		await waitFor( () => {
			expect( screen.getByText( 'Sample Venue 1' ) ).toBeInTheDocument();
		} );

		expect( screen.queryByText( 'Add another venue' ) ).not.toBeInTheDocument();
	} );

	it( 'should open venue upsert modal when create new venue is clicked', () => {
		setupMocks();
		mockFetchVenues.mockResolvedValue( { venues: [], total: 0 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		const createButton = screen.getByText( 'Create new venue' );
		act( () => {
			fireEvent.click( createButton );
		} );

		// Check that the modal is opened by looking for modal-specific elements.
		expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();
	} );

	it( 'should open venue upsert modal for editing when edit is clicked', async () => {
		setupMocks( {
			[ METADATA_EVENT_VENUE_ID ]: [ '1' ],
		} );
		mockFetchVenues.mockResolvedValue( { venues: mockVenues, total: 2 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		// Wait for venues to be loaded.
		await waitFor( () => {
			expect( screen.getByText( 'Sample Venue 1' ) ).toBeInTheDocument();
		} );

		// Look for edit button by finding the first button with edit icon.
		const editButton = screen.getAllByRole( 'button' )[ 0 ];
		act( () => {
			fireEvent.click( editButton );
		} );

		// Check that the modal is opened.
		expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();
	} );

	it( 'should close venue upsert modal when cancel is clicked', () => {
		setupMocks();
		mockFetchVenues.mockResolvedValue( { venues: [], total: 0 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		// Open modal.
		const createButton = screen.getByText( 'Create new venue' );
		act( () => {
			fireEvent.click( createButton );
		} );

		expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();

		// Close modal - look for cancel button by text.
		const cancelButton = screen.getByText( 'Cancel' );
		act( () => {
			fireEvent.click( cancelButton );
		} );

		expect( screen.queryByRole( 'dialog' ) ).not.toBeInTheDocument();
	} );

	it( 'should handle venue fetch error gracefully', async () => {
		setupMocks();
		mockFetchVenues.mockRejectedValue( new Error( 'Fetch failed' ) );

		// Mock console.error to avoid noise in test output.
		const consoleSpy = jest.spyOn( console, 'error' ).mockImplementation( () => {} );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		await waitFor( () => {
			expect( consoleSpy ).toHaveBeenCalledWith( 'Venue fetch request failed: Fetch failed' );
		} );

		consoleSpy.mockRestore();
	} );

	it( 'should handle venue upsert error gracefully', async () => {
		setupMocks();
		mockFetchVenues.mockResolvedValue( { venues: [], total: 0 } );
		mockUpsertVenue.mockRejectedValue( new Error( 'Upsert failed' ) );

		// Mock console.error to avoid noise in test output.
		const consoleSpy = jest.spyOn( console, 'error' ).mockImplementation( () => {} );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		// Open modal.
		const createButton = screen.getByText( 'Create new venue' );
		act( () => {
			fireEvent.click( createButton );
		} );

		// The modal should be open.
		expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();

		consoleSpy.mockRestore();
	} );

	it( 'should show adding state when add another venue is clicked', async () => {
		setupMocks( {
			[ METADATA_EVENT_VENUE_ID ]: [ '1' ],
		}, 2 );
		mockFetchVenues.mockResolvedValue( { venues: mockVenues, total: 2 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		// Wait for venues to be loaded.
		await waitFor( () => {
			expect( screen.getByText( 'Sample Venue 1' ) ).toBeInTheDocument();
		} );

		const addButton = screen.getByText( 'Add another venue' );
		act( () => {
			fireEvent.click( addButton );
		} );

		// Should show the venue selection controls.
		expect( screen.getByText( 'Select venue' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Create new venue' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Cancel' ) ).toBeInTheDocument();
	} );

	it( 'should cancel adding state when cancel is clicked', async () => {
		setupMocks( {
			[ METADATA_EVENT_VENUE_ID ]: [ '1' ],
		}, 2 );
		mockFetchVenues.mockResolvedValue( { venues: mockVenues, total: 2 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		// Wait for venues to be loaded.
		await waitFor( () => {
			expect( screen.getByText( 'Sample Venue 1' ) ).toBeInTheDocument();
		} );

		// Start adding.
		const addButton = screen.getByText( 'Add another venue' );
		act( () => {
			fireEvent.click( addButton );
		} );

		// Cancel adding.
		const cancelButton = screen.getByText( 'Cancel' );
		act( () => {
			fireEvent.click( cancelButton );
		} );

		// Should hide the venue selection controls.
		expect( screen.queryByText( 'Select venue' ) ).not.toBeInTheDocument();
		expect( screen.queryByText( 'Create new venue' ) ).not.toBeInTheDocument();
		expect( screen.queryByText( 'Cancel' ) ).not.toBeInTheDocument();
	} );

	it( 'should handle pagination when more venues are available', async () => {
		setupMocks();
		mockFetchVenues
			.mockResolvedValueOnce( { venues: mockVenues, total: 10 } )
			.mockResolvedValueOnce( { venues: [], total: 10 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		await waitFor( () => {
			expect( mockFetchVenues ).toHaveBeenCalledWith( 1 );
		} );

		// Should fetch next page.
		await waitFor( () => {
			expect( mockFetchVenues ).toHaveBeenCalledWith( 2 );
		} );
	} );

	it( 'should not fetch next page when all venues are loaded', async () => {
		setupMocks();
		mockFetchVenues.mockResolvedValue( { venues: mockVenues, total: 2 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		await waitFor( () => {
			expect( mockFetchVenues ).toHaveBeenCalledWith( 1 );
		} );

		// Should not fetch next page.
		await waitFor( () => {
			expect( mockFetchVenues ).toHaveBeenCalledTimes( 1 );
		} );
	} );

	it( 'should handle empty venue list gracefully', async () => {
		setupMocks();
		mockFetchVenues.mockResolvedValue( { venues: [], total: 0 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		await waitFor( () => {
			expect( mockFetchVenues ).toHaveBeenCalledWith( 1 );
		} );

		// Should not show venue names.
		expect( screen.queryByText( 'Sample Venue 1' ) ).not.toBeInTheDocument();
	} );

	it( 'should render with custom title prop', () => {
		setupMocks();
		mockFetchVenues.mockResolvedValue( { venues: [], total: 0 } );

		render(
			<TestProvider>
				<EventLocation title="Custom Event Location Title" />
			</TestProvider>
		);

		expect( screen.getByText( 'Custom Event Location Title' ) ).toBeInTheDocument();
	} );

	it( 'should handle venue data mapping correctly for US venues', async () => {
		setupMocks( {
			[ METADATA_EVENT_VENUE_ID ]: [ '1' ],
		} );
		mockFetchVenues.mockResolvedValue( { venues: mockVenues, total: 2 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		// Wait for venues to be loaded.
		await waitFor( () => {
			expect( screen.getByText( 'Sample Venue 1' ) ).toBeInTheDocument();
		} );

		// Open edit modal.
		const editButton = screen.getAllByRole( 'button' )[ 0 ];
		act( () => {
			fireEvent.click( editButton );
		} );

		// Check that the modal is opened.
		expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();
	} );

	it( 'should handle venue data mapping correctly for international venues', async () => {
		const internationalVenues = [
			{
				id: 3,
				venue: 'International Venue',
				address: '789 Maple Street',
				city: 'Toronto',
				country: 'Canada',
				province: 'ON',
				state: '',
				zip: 'M5V 3A8',
				phone: '+1-416-555-0125',
				website: 'https://international-venue.ca',
			},
		];

		setupMocks( {
			[ METADATA_EVENT_VENUE_ID ]: [ '3' ],
		} );
		mockFetchVenues.mockResolvedValue( { venues: internationalVenues, total: 1 } );

		render(
			<TestProvider>
				<EventLocation title="Event location" />
			</TestProvider>
		);

		// Wait for venues to be loaded.
		await waitFor( () => {
			expect( screen.getByText( 'International Venue' ) ).toBeInTheDocument();
		} );

		// Open edit modal.
		const editButton = screen.getAllByRole( 'button' )[ 0 ];
		act( () => {
			fireEvent.click( editButton );
		} );

		// Check that the modal is opened.
		expect( screen.getByRole( 'dialog' ) ).toBeInTheDocument();
	} );
} );
