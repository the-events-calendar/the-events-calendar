import { fetchVenues, upsertVenue } from '../../../src/resources/packages/classy/api/venues';
import { afterEach, beforeEach, describe, expect, it, jest } from '@jest/globals';
import { FetchedVenue } from '../../../src/resources/packages/classy/types/FetchedVenue';
import { VenueData } from '../../../src/resources/packages/classy/types/VenueData';
import apiFetch from '@wordpress/api-fetch';

// Mock the @wordpress/api-fetch module.
jest.mock( '@wordpress/api-fetch' );

describe( 'venues API', () => {
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

	describe( 'fetchVenues', () => {
		it( 'should fetch venues successfully and map them correctly', async () => {
			// Mock response data.
			const mockVenueData = [
				{
					id: 1,
					link: 'https://example.com/venue-1',
					title: {
						rendered: 'Grand Convention Center',
					},
					address: '123 Main Street',
					city: 'New York',
					country: 'United States',
					state_province: 'NY',
					state: 'NY',
					province: '',
					zip: '10001',
					phone: '555-1234',
					website: 'https://grandconvention.com',
					directions_link: 'https://maps.example.com',
					// Additional fields that should be ignored in mapping.
					date: '2024-01-01',
					status: 'publish',
					slug: 'grand-convention-center',
				},
				{
					id: 2,
					link: 'https://example.com/venue-2',
					title: {
						rendered: 'City Hall',
					},
					address: '456 Oak Avenue',
					city: 'Los Angeles',
					country: 'United States',
					state_province: 'CA',
					state: 'CA',
					province: '',
					zip: '90001',
					phone: '555-5678',
					website: 'https://cityhall.la.gov',
					directions_link: 'https://maps.example.com',
					// Additional fields.
					date: '2024-01-02',
					status: 'publish',
					slug: 'city-hall',
				},
			];

			// Create a mock Response object with headers.
			const mockResponse = {
				json: ( jest.fn() as any ).mockResolvedValue( mockVenueData ),
				headers: {
					has: ( jest.fn() as any ).mockImplementation( ( key: string ) => key === 'x-wp-total' ),
					get: ( jest.fn() as any ).mockImplementation( ( key: string ) =>
						key === 'x-wp-total' ? '50' : null
					),
				},
			};

			// Mock apiFetch to return our mock response.
			mockApiFetch.mockResolvedValue( mockResponse );

			// Call the function.
			const result = await fetchVenues( 3 );

			// Verify the API was called with correct parameters.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/venues?page=3',
				parse: false,
			} );

			// Verify the result structure.
			expect( result ).toHaveProperty( 'venues' );
			expect( result ).toHaveProperty( 'total' );
			expect( result.total ).toBe( '50' );
			expect( result.venues ).toHaveLength( 2 );

			// Verify the mapping of venues.
			const expectedVenues: FetchedVenue[] = [
				{
					id: 1,
					venue: 'Grand Convention Center',
					address: '123 Main Street',
					city: 'New York',
					country: 'United States',
					province: '',
					state: 'NY',
					zip: '10001',
					phone: '555-1234',
					website: 'https://grandconvention.com',
				},
				{
					id: 2,
					venue: 'City Hall',
					address: '456 Oak Avenue',
					city: 'Los Angeles',
					country: 'United States',
					province: '',
					state: 'CA',
					zip: '90001',
					phone: '555-5678',
					website: 'https://cityhall.la.gov',
				},
			];

			expect( result.venues ).toEqual( expectedVenues );
		} );

		it( 'should handle missing x-wp-total header gracefully', async () => {
			const mockVenueData = [
				{
					id: 1,
					link: 'https://example.com/venue-1',
					title: { rendered: 'Test Venue' },
					address: '',
					city: '',
					country: '',
					state_province: '',
					state: '',
					province: '',
					zip: '',
					phone: '',
					website: '',
					directions_link: '',
				},
			];

			// Create response without x-wp-total header.
			const mockResponse = {
				json: ( jest.fn() as any ).mockResolvedValue( mockVenueData ),
				headers: {
					has: ( jest.fn() as any ).mockReturnValue( false ),
					get: ( jest.fn() as any ).mockReturnValue( null ),
				},
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await fetchVenues( 1 );

			// Should default to 0 when header is missing.
			expect( result.total ).toBe( 0 );
			expect( result.venues ).toHaveLength( 1 );
		} );

		it( 'should reject when response does not have json method', async () => {
			// Create a response without json method.
			const mockResponse = {
				headers: {
					has: ( jest.fn() as any ).mockReturnValue( false ),
					get: ( jest.fn() as any ).mockReturnValue( null ),
				},
				// Intentionally missing json method.
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			await expect( fetchVenues( 1 ) ).rejects.toEqual( mockResponse );
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

			await expect( fetchVenues( 1 ) ).rejects.toThrow( 'Venues fetch request did not return an object.' );
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

			await expect( fetchVenues( 1 ) ).rejects.toThrow( 'Venues fetch request did not return an object.' );
		} );

		it( 'should handle API fetch errors', async () => {
			const mockError = new Error( 'Network timeout' );
			mockApiFetch.mockRejectedValue( mockError );

			await expect( fetchVenues( 2 ) ).rejects.toThrow( 'Failed to fetch venue Network timeout' );
		} );

		it( 'should handle empty venue list', async () => {
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

			const result = await fetchVenues( 1 );

			expect( result.venues ).toEqual( [] );
			expect( result.total ).toBe( '0' );
		} );

		it( 'should correctly map venues with international addresses', async () => {
			const mockVenueData = [
				{
					id: 3,
					link: 'https://example.com/venue-3',
					title: {
						rendered: 'Toronto Conference Centre',
					},
					address: '789 Queen Street',
					city: 'Toronto',
					country: 'Canada',
					state_province: 'ON',
					state: '',
					province: 'ON',
					zip: 'M5H 2M9',
					phone: '+1-416-555-9999',
					website: 'https://torontocc.ca',
					directions_link: '',
				},
			];

			const mockResponse = {
				json: ( jest.fn() as any ).mockResolvedValue( mockVenueData ),
				headers: {
					has: ( jest.fn() as any ).mockReturnValue( false ),
					get: ( jest.fn() as any ).mockReturnValue( null ),
				},
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await fetchVenues( 1 );

			expect( result.venues[ 0 ] ).toEqual( {
				id: 3,
				venue: 'Toronto Conference Centre',
				address: '789 Queen Street',
				city: 'Toronto',
				country: 'Canada',
				province: 'ON',
				state: '',
				zip: 'M5H 2M9',
				phone: '+1-416-555-9999',
				website: 'https://torontocc.ca',
			} );
		} );
	} );

	describe( 'upsertVenue', () => {
		it( 'should create a new venue when id is null', async () => {
			const newVenueData: VenueData = {
				id: null,
				name: 'New Convention Center',
				address: '100 Park Avenue',
				city: 'Seattle',
				country: 'United States',
				countryCode: 'US',
				province: '',
				stateprovince: 'WA',
				zip: '98101',
				phone: '206-555-1234',
				website: 'https://seattlecc.com',
			};

			const mockResponse = {
				id: 42,
				title: { rendered: 'New Convention Center' },
				address: '100 Park Avenue',
				city: 'Seattle',
				country: 'United States',
				state_province: 'WA',
				state: 'WA',
				province: '',
				zip: '98101',
				phone: '206-555-1234',
				website: 'https://seattlecc.com',
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertVenue( newVenueData );

			// Verify correct API call for creation.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/venues',
				method: 'POST',
				data: {
					title: 'New Convention Center',
					status: 'publish',
					address: '100 Park Avenue',
					city: 'Seattle',
					country: 'United States',
					state_province: 'WA',
					state: 'WA',
					zip: '98101',
					phone: '206-555-1234',
					website: 'https://seattlecc.com',
				},
			} );

			expect( result ).toBe( 42 );
		} );

		it( 'should create a new venue when id is 0', async () => {
			const newVenueData: VenueData = {
				id: 0,
				name: 'Another New Venue',
				address: '',
				city: '',
				country: '',
				countryCode: '',
				province: '',
				stateprovince: '',
				zip: '',
				phone: '',
				website: '',
			};

			const mockResponse = {
				id: 43,
				title: { rendered: 'Another New Venue' },
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertVenue( newVenueData );

			// Verify empty optional fields are not included, except province which is set for non-US.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/venues',
				method: 'POST',
				data: {
					title: 'Another New Venue',
					status: 'publish',
					province: '', // Set because countryCode is not 'US'.
				},
			} );

			expect( result ).toBe( 43 );
		} );

		it( 'should update an existing venue when id is provided', async () => {
			const existingVenueData: VenueData = {
				id: 15,
				name: 'Updated Venue',
				address: '200 Broadway',
				city: 'Portland',
				country: 'United States',
				countryCode: 'US',
				province: '',
				stateprovince: 'OR',
				zip: '97201',
				phone: '503-555-9999',
				website: 'https://updatedvenue.com',
			};

			const mockResponse = {
				id: 15,
				title: { rendered: 'Updated Venue' },
				address: '200 Broadway',
				city: 'Portland',
				country: 'United States',
				state_province: 'OR',
				state: 'OR',
				province: '',
				zip: '97201',
				phone: '503-555-9999',
				website: 'https://updatedvenue.com',
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertVenue( existingVenueData );

			// Verify correct API call for update.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/venues/15',
				method: 'PUT',
				data: {
					title: 'Updated Venue',
					status: 'publish',
					address: '200 Broadway',
					city: 'Portland',
					country: 'United States',
					state_province: 'OR',
					state: 'OR',
					zip: '97201',
					phone: '503-555-9999',
					website: 'https://updatedvenue.com',
				},
			} );

			expect( result ).toBe( 15 );
		} );

		it( 'should handle Canadian venues with province instead of state', async () => {
			const canadianVenueData: VenueData = {
				id: null,
				name: 'Montreal Convention Centre',
				address: '1001 Place Jean-Paul-Riopelle',
				city: 'Montreal',
				country: 'Canada',
				countryCode: 'CA',
				province: 'QC',
				stateprovince: 'QC',
				zip: 'H2Z 1H2',
				phone: '+1-514-555-1234',
				website: 'https://montrealcc.com',
			};

			const mockResponse = {
				id: 50,
				title: { rendered: 'Montreal Convention Centre' },
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertVenue( canadianVenueData );

			// Verify that for non-US venues, province is set instead of state.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/venues',
				method: 'POST',
				data: {
					title: 'Montreal Convention Centre',
					status: 'publish',
					address: '1001 Place Jean-Paul-Riopelle',
					city: 'Montreal',
					country: 'Canada',
					state_province: 'QC',
					province: 'QC',
					zip: 'H2Z 1H2',
					phone: '+1-514-555-1234',
					website: 'https://montrealcc.com',
				},
			} );

			expect( result ).toBe( 50 );
		} );

		it( 'should handle partial data with some empty fields', async () => {
			const partialVenueData: VenueData = {
				id: null,
				name: 'Partial Venue',
				address: '300 Main St',
				city: 'Boston',
				country: '',
				countryCode: '',
				province: '',
				stateprovince: '',
				zip: '',
				phone: '',
				website: '',
			};

			const mockResponse = {
				id: 44,
				title: { rendered: 'Partial Venue' },
				address: '300 Main St',
				city: 'Boston',
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertVenue( partialVenueData );

			// Verify only non-empty fields are included, except province which is set for non-US.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/venues',
				method: 'POST',
				data: {
					title: 'Partial Venue',
					status: 'publish',
					address: '300 Main St',
					city: 'Boston',
					province: '', // Set because countryCode is not 'US'.
				},
			} );

			expect( result ).toBe( 44 );
		} );

		it( 'should reject when response is not a valid object', async () => {
			const venueData: VenueData = {
				id: null,
				name: 'Test Venue',
				address: '',
				city: '',
				country: '',
				countryCode: '',
				province: '',
				stateprovince: '',
				zip: '',
				phone: '',
				website: '',
			};

			// Response is not an object.
			mockApiFetch.mockResolvedValue( null );

			await expect( upsertVenue( venueData ) ).rejects.toThrow(
				'Venue creation request did not return a valid venue object.'
			);
		} );

		it( 'should reject when response does not contain an id', async () => {
			const venueData: VenueData = {
				id: 10,
				name: 'Test Venue',
				address: '',
				city: '',
				country: '',
				countryCode: '',
				province: '',
				stateprovince: '',
				zip: '',
				phone: '',
				website: '',
			};

			// Response missing id field.
			const mockResponse = {
				title: { rendered: 'Test Venue' },
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			await expect( upsertVenue( venueData ) ).rejects.toThrow(
				'Venue update request did not return a valid venue object.'
			);
		} );

		it( 'should handle creation errors', async () => {
			const venueData: VenueData = {
				id: null,
				name: 'Failed Venue',
				address: '',
				city: '',
				country: '',
				countryCode: '',
				province: '',
				stateprovince: '',
				zip: '',
				phone: '',
				website: '',
			};

			const mockError = new Error( 'Insufficient permissions' );
			mockApiFetch.mockRejectedValue( mockError );

			await expect( upsertVenue( venueData ) ).rejects.toThrow(
				'Failed to create venue: Insufficient permissions'
			);
		} );

		it( 'should handle update errors', async () => {
			const venueData: VenueData = {
				id: 99,
				name: 'Failed Update',
				address: '',
				city: '',
				country: '',
				countryCode: '',
				province: '',
				stateprovince: '',
				zip: '',
				phone: '',
				website: '',
			};

			const mockError = new Error( 'Venue not found' );
			mockApiFetch.mockRejectedValue( mockError );

			await expect( upsertVenue( venueData ) ).rejects.toThrow( 'Failed to update venue: Venue not found' );
		} );

		it( 'should handle US venue with all optional fields provided', async () => {
			const fullVenueData: VenueData = {
				id: null,
				name: 'Full Featured Venue',
				address: '400 Fifth Avenue',
				city: 'San Francisco',
				country: 'United States',
				countryCode: 'US',
				province: '',
				stateprovince: 'CA',
				zip: '94102',
				phone: '+1-415-555-1234',
				website: 'https://www.fullfeaturedvenue.com',
			};

			const mockResponse = {
				id: 100,
				title: { rendered: 'Full Featured Venue' },
				address: '400 Fifth Avenue',
				city: 'San Francisco',
				country: 'United States',
				state_province: 'CA',
				state: 'CA',
				province: '',
				zip: '94102',
				phone: '+1-415-555-1234',
				website: 'https://www.fullfeaturedvenue.com',
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertVenue( fullVenueData );

			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/venues',
				method: 'POST',
				data: {
					title: 'Full Featured Venue',
					status: 'publish',
					address: '400 Fifth Avenue',
					city: 'San Francisco',
					country: 'United States',
					state_province: 'CA',
					state: 'CA',
					zip: '94102',
					phone: '+1-415-555-1234',
					website: 'https://www.fullfeaturedvenue.com',
				},
			} );

			expect( result ).toBe( 100 );
		} );

		it( 'should handle international venue with all fields', async () => {
			const internationalVenueData: VenueData = {
				id: null,
				name: 'London Exhibition Centre',
				address: '1 Exhibition Road',
				city: 'London',
				country: 'United Kingdom',
				countryCode: 'GB',
				province: 'Greater London',
				stateprovince: 'Greater London',
				zip: 'SW7 2HE',
				phone: '+44-20-7942-2000',
				website: 'https://www.londonexhibition.co.uk',
			};

			const mockResponse = {
				id: 101,
				title: { rendered: 'London Exhibition Centre' },
				status: 'publish',
			};

			mockApiFetch.mockResolvedValue( mockResponse );

			const result = await upsertVenue( internationalVenueData );

			// Verify that for non-US venues, province is set.
			expect( mockApiFetch ).toHaveBeenCalledWith( {
				path: '/tec/v1/venues',
				method: 'POST',
				data: {
					title: 'London Exhibition Centre',
					status: 'publish',
					address: '1 Exhibition Road',
					city: 'London',
					country: 'United Kingdom',
					state_province: 'Greater London',
					province: 'Greater London',
					zip: 'SW7 2HE',
					phone: '+44-20-7942-2000',
					website: 'https://www.londonexhibition.co.uk',
				},
			} );

			expect( result ).toBe( 101 );
		} );
	} );
} );
