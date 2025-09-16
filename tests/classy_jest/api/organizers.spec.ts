import { fetchOrganizers, upsertOrganizer } from '../../../src/resources/packages/classy/api/organizers';
import { afterEach, beforeEach, describe, expect, it, jest } from '@jest/globals';
import { FetchedOrganizer } from '../../../src/resources/packages/classy/types/FetchedOrganizer';
import { OrganizerData } from '../../../src/resources/packages/classy/types/OrganizerData';
import apiFetch from '@wordpress/api-fetch';

// Mock the @wordpress/api-fetch module.
jest.mock( '@wordpress/api-fetch' );

describe( 'organizers API', () => {
	const mockApiFetch = apiFetch as jest.MockedFunction< typeof apiFetch >;

	beforeEach( () => {
		jest.resetModules();
		jest.clearAllMocks();
	} );

	afterEach( () => {
		jest.resetAllMocks();
		jest.restoreAllMocks();
		jest.resetModules();
	} );

	describe( 'fetchOrganizers', () => {
		it( 'should fetch organizers successfully and map them correctly', async () => {
			// Mock response data.
			const mockOrganizerData = [
				{
					id: 1,
					link: 'https://example.com/organizer-1',
					title: {
						rendered: 'Test Organizer 1',
					},
					phone: '123-456-7890',
					website: 'https://organizer1.com',
					email: 'contact@organizer1.com',
					// Additional fields that should be ignored in mapping.
					date: '2024-01-01',
					status: 'publish',
					slug: 'test-organizer-1',
				},
				{
					id: 2,
					link: 'https://example.com/organizer-2',
					title: {
						rendered: 'Test Organizer 2',
					},
					phone: '098-765-4321',
					website: 'https://organizer2.com',
					email: 'info@organizer2.com',
					// Additional fields.
					date: '2024-01-02',
					status: 'publish',
					slug: 'test-organizer-2',
				},
			];

			// Create a mock Response object with headers.
			const mockResponse = {
				json: ( jest.fn() as any ).mockResolvedValue( mockOrganizerData ),
				headers: {
					has: ( jest.fn() as any ).mockImplementation( ( key: string ) => key === 'x-wp-total' ),
					get: ( jest.fn() as any ).mockImplementation( ( key: string ) =>
						key === 'x-wp-total' ? '25' : null
					),
				},
			};

			// Mock apiFetch to return our mock response.
			mockApiFetch.mockResolvedValue( mockResponse );

			// Call the function.
			const result = await fetchOrganizers( 2 );

			// Verify the API was called with correct parameters.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/organizers?page=2',
				parse: false,
			} );

			// Verify the result structure.
			expect( result ).toHaveProperty( 'organizers' );
			expect( result ).toHaveProperty( 'total' );
			expect( result.total ).toBe( '25' );
			expect( result.organizers ).toHaveLength( 2 );

			// Verify the mapping of organizers.
			const expectedOrganizers: FetchedOrganizer[] = [
				{
					id: 1,
					url: 'https://example.com/organizer-1',
					organizer: 'Test Organizer 1',
					phone: '123-456-7890',
					website: 'https://organizer1.com',
					email: 'contact@organizer1.com',
				},
				{
					id: 2,
					url: 'https://example.com/organizer-2',
					organizer: 'Test Organizer 2',
					phone: '098-765-4321',
					website: 'https://organizer2.com',
					email: 'info@organizer2.com',
				},
			];

			expect( result.organizers ).toEqual( expectedOrganizers );
		} );

		it( 'should handle missing x-wp-total header gracefully', async () => {
			const mockOrganizerData = [
				{
					id: 1,
					link: 'https://example.com/organizer-1',
					title: { rendered: 'Test Organizer' },
					phone: '',
					website: '',
					email: '',
				},
			];

			// Create response without x-wp-total header.
			const mockResponse = {
				json: ( jest.fn() as any ).mockResolvedValue( mockOrganizerData ),
				headers: {
					has: ( jest.fn() as any ).mockReturnValue( false ),
					get: ( jest.fn() as any ).mockReturnValue( null ),
				},
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await fetchOrganizers( 1 );

			// Should default to 0 when header is missing.
			expect( result.total ).toBe( 0 );
			expect( result.organizers ).toHaveLength( 1 );
		} );

		it( 'should reject when response does not have json method', async () => {
			// Create a response without json method.
			const mockResponse = {
				headers: new Headers(),
				// Intentionally missing json method.
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			await expect( fetchOrganizers( 1 ) ).rejects.toEqual( mockResponse );
		} );

		it( 'should reject when response is not an array', async () => {
			// Return an object instead of an array.
			const mockResponse = {
				json: ( jest.fn() as any ).mockResolvedValue( { not: 'an array' } ),
				headers: {
					has: ( jest.fn() as any ).mockReturnValue( false ),
					get: ( jest.fn() as any ).mockReturnValue( null ),
				},
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			await expect( fetchOrganizers( 1 ) ).rejects.toThrow(
				'Organizers fetch request did not return an object.'
			);
		} );

		it( 'should reject when response is not an object', async () => {
			// Return a string instead of an object.
			const mockResponse = {
				json: ( jest.fn() as any ).mockResolvedValue( 'string response' ),
				headers: {
					has: ( jest.fn() as any ).mockReturnValue( false ),
					get: ( jest.fn() as any ).mockReturnValue( null ),
				},
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			await expect( fetchOrganizers( 1 ) ).rejects.toThrow(
				'Organizers fetch request did not return an object.'
			);
		} );

		it( 'should handle API fetch errors', async () => {
			const mockError = new Error( 'Network error' );
			mockApiFetch.mockRejectedValue( mockError );

			await expect( fetchOrganizers( 1 ) ).rejects.toThrow( 'Failed to fetch organizer Network error' );
		} );

		it( 'should handle empty organizer list', async () => {
			const mockResponse = {
				json: ( jest.fn() as any ).mockResolvedValue( [] ),
				headers: {
					has: ( jest.fn() as any ).mockImplementation( ( key: string ) => key === 'x-wp-total' ),
					get: ( jest.fn() as any ).mockImplementation( ( key: string ) =>
						key === 'x-wp-total' ? '0' : null
					),
				},
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await fetchOrganizers( 1 );

			expect( result.organizers ).toEqual( [] );
			expect( result.total ).toBe( '0' );
		} );
	} );

	describe( 'upsertOrganizer', () => {
		it( 'should create a new organizer when id is null', async () => {
			const newOrganizerData: OrganizerData = {
				id: null,
				name: 'New Organizer',
				phone: '555-1234',
				email: 'new@organizer.com',
				website: 'https://neworganizer.com',
			};

			const mockResponse = {
				id: 42,
				title: { rendered: 'New Organizer' },
				phone: '555-1234',
				email: 'new@organizer.com',
				website: 'https://neworganizer.com',
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertOrganizer( newOrganizerData );

			// Verify correct API call for creation.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/organizers',
				method: 'POST',
				data: {
					title: 'New Organizer',
					status: 'publish',
					phone: '555-1234',
					email: 'new@organizer.com',
					website: 'https://neworganizer.com',
				},
			} );

			expect( result ).toBe( 42 );
		} );

		it( 'should create a new organizer when id is 0', async () => {
			const newOrganizerData: OrganizerData = {
				id: 0,
				name: 'Another New Organizer',
				phone: '',
				email: '',
				website: '',
			};

			const mockResponse = {
				id: 43,
				title: { rendered: 'Another New Organizer' },
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertOrganizer( newOrganizerData );

			// Verify empty optional fields are not included.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/organizers',
				method: 'POST',
				data: {
					title: 'Another New Organizer',
					status: 'publish',
				},
			} );

			expect( result ).toBe( 43 );
		} );

		it( 'should update an existing organizer when id is provided', async () => {
			const existingOrganizerData: OrganizerData = {
				id: 15,
				name: 'Updated Organizer',
				phone: '555-9999',
				email: 'updated@organizer.com',
				website: 'https://updatedorganizer.com',
			};

			const mockResponse = {
				id: 15,
				title: { rendered: 'Updated Organizer' },
				phone: '555-9999',
				email: 'updated@organizer.com',
				website: 'https://updatedorganizer.com',
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertOrganizer( existingOrganizerData );

			// Verify correct API call for update.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/organizers/15',
				method: 'PUT',
				data: {
					title: 'Updated Organizer',
					status: 'publish',
					phone: '555-9999',
					email: 'updated@organizer.com',
					website: 'https://updatedorganizer.com',
				},
			} );

			expect( result ).toBe( 15 );
		} );

		it( 'should handle partial data with some empty fields', async () => {
			const partialOrganizerData: OrganizerData = {
				id: null,
				name: 'Partial Organizer',
				phone: '555-0000',
				email: '',
				website: '',
			};

			const mockResponse = {
				id: 44,
				title: { rendered: 'Partial Organizer' },
				phone: '555-0000',
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertOrganizer( partialOrganizerData );

			// Verify only non-empty fields are included.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/organizers',
				method: 'POST',
				data: {
					title: 'Partial Organizer',
					status: 'publish',
					phone: '555-0000',
				},
			} );

			expect( result ).toBe( 44 );
		} );

		it( 'should reject when response is not a valid object', async () => {
			const organizerData: OrganizerData = {
				id: null,
				name: 'Test Organizer',
				phone: '',
				email: '',
				website: '',
			};

			// Response is not an object.
			mockApiFetch.mockResolvedValue( null );

			await expect( upsertOrganizer( organizerData ) ).rejects.toThrow(
				'Organizer creation request did not return a valid organizer object.'
			);
		} );

		it( 'should reject when response does not contain an id', async () => {
			const organizerData: OrganizerData = {
				id: 10,
				name: 'Test Organizer',
				phone: '',
				email: '',
				website: '',
			};

			// Response missing id field.
			const mockResponse = {
				title: { rendered: 'Test Organizer' },
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			await expect( upsertOrganizer( organizerData ) ).rejects.toThrow(
				'Organizer update request did not return a valid organizer object.'
			);
		} );

		it( 'should handle creation errors', async () => {
			const organizerData: OrganizerData = {
				id: null,
				name: 'Failed Organizer',
				phone: '',
				email: '',
				website: '',
			};

			const mockError = new Error( 'Permission denied' );
			mockApiFetch.mockRejectedValue( mockError );

			await expect( upsertOrganizer( organizerData ) ).rejects.toThrow(
				'Failed to create organizer: Permission denied'
			);
		} );

		it( 'should handle update errors', async () => {
			const organizerData: OrganizerData = {
				id: 99,
				name: 'Failed Update',
				phone: '',
				email: '',
				website: '',
			};

			const mockError = new Error( 'Not found' );
			mockApiFetch.mockRejectedValue( mockError );

			await expect( upsertOrganizer( organizerData ) ).rejects.toThrow( 'Failed to update organizer: Not found' );
		} );

		it( 'should handle organizer with all optional fields provided', async () => {
			const fullOrganizerData: OrganizerData = {
				id: null,
				name: 'Full Featured Organizer',
				phone: '+1-555-123-4567',
				email: 'contact@fullorganizer.com',
				website: 'https://www.fullorganizer.com',
			};

			const mockResponse = {
				id: 100,
				title: { rendered: 'Full Featured Organizer' },
				phone: '+1-555-123-4567',
				email: 'contact@fullorganizer.com',
				website: 'https://www.fullorganizer.com',
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertOrganizer( fullOrganizerData );

			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/organizers',
				method: 'POST',
				data: {
					title: 'Full Featured Organizer',
					status: 'publish',
					phone: '+1-555-123-4567',
					email: 'contact@fullorganizer.com',
					website: 'https://www.fullorganizer.com',
				},
			} );

			expect( result ).toBe( 100 );
		} );
	} );
} );
