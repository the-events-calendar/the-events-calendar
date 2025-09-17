import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, jest, test } from '@jest/globals';
import apiFetch from '@wordpress/api-fetch';
import { fetchVenues, upsertVenue } from '@tec/events/classy/api';
import { FetchedVenue } from '@tec/events/classy/types/FetchedVenue';
import { VenueData } from '@tec/events/classy/types/VenueData';
import { PostStatus } from '@tec/common/classy/types/Api';

jest.mock( '@wordpress/api-fetch', () => ( {
	__esModule: true,
	default: jest.fn(),
} ) );

// Mock data structures for expected results
const mockVenueResponse = {
	id: 1,
	link: 'https://example.com/venues/sample-venue-1/',
	title: {
		rendered: 'Sample Venue 1',
	},
	address: '123 Main Street',
	city: 'New York',
	country: 'United States',
	state_province: 'NY',
	state: 'NY',
	province: '',
	zip: '10001',
	phone: '+1-555-0123',
	website: 'https://sample-venue-1.com',
	directions_link: 'https://maps.google.com/sample-venue-1',
	date: '2024-01-01T12:00:00+00:00',
	date_gmt: '2024-01-01T12:00:00Z',
	guid: {
		rendered: 'https://example.com/venues/sample-venue-1/',
	},
	modified: '2024-01-01T12:00:00+00:00',
	modified_gmt: '2024-01-01T12:00:00Z',
	slug: 'sample-venue-1',
	status: 'publish' as PostStatus,
	type: 'tribe_venue',
	content: {
		rendered: 'This is a sample venue description.',
		protected: false,
	},
	template: '',
};

const mockVenueResponse2 = {
	...mockVenueResponse,
	id: 2,
	link: 'https://example.com/venues/sample-venue-2/',
	title: {
		rendered: 'Sample Venue 2',
	},
	address: '456 Oak Avenue',
	city: 'Los Angeles',
	country: 'United States',
	state_province: 'CA',
	state: 'CA',
	province: '',
	zip: '90210',
	phone: '+1-555-0124',
	website: 'https://sample-venue-2.com',
	slug: 'sample-venue-2',
};

const mockVenueResponseInternational = {
	...mockVenueResponse,
	id: 3,
	link: 'https://example.com/venues/sample-venue-3/',
	title: {
		rendered: 'Sample International Venue',
	},
	address: '789 Maple Street',
	city: 'Toronto',
	country: 'Canada',
	state_province: 'ON',
	state: '',
	province: 'ON',
	zip: 'M5V 3A8',
	phone: '+1-416-555-0125',
	website: 'https://sample-venue-3.ca',
	slug: 'sample-venue-3',
};

const mockMappedVenues: FetchedVenue[] = [
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

const mockVenueDataForCreate: VenueData = {
	id: null,
	name: 'New Venue',
	address: '789 Pine Street',
	city: 'Chicago',
	country: 'United States',
	countryCode: 'US',
	province: '',
	stateprovince: 'IL',
	zip: '60601',
	phone: '+1-312-555-9999',
	website: 'https://new-venue.com',
};

const mockVenueDataForUpdate: VenueData = {
	id: 1,
	name: 'Updated Venue',
	address: '321 Elm Street',
	city: 'Boston',
	country: 'United States',
	countryCode: 'US',
	province: '',
	stateprovince: 'MA',
	zip: '02101',
	phone: '+1-617-555-8888',
	website: 'https://updated-venue.com',
};

const mockVenueDataInternational: VenueData = {
	id: null,
	name: 'International Venue',
	address: '555 Queen Street',
	city: 'Vancouver',
	country: 'Canada',
	countryCode: 'CA',
	province: 'BC',
	stateprovince: 'BC',
	zip: 'V6B 1A1',
	phone: '+1-604-555-7777',
	website: 'https://international-venue.ca',
};

const mockVenueDataMinimal: VenueData = {
	id: null,
	name: 'Minimal Venue',
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

const mockExpectedFetchResult = {
	venues: mockMappedVenues,
	total: "2",
};

const mockApiResponse = {
	...mockVenueResponse,
};

// Mock Response object for fetchVenues
const createMockResponse = ( data: any[], total: number ) => {
	return {
		json: jest.fn( () => Promise.resolve( data ) ),
		headers: {
			has: jest.fn().mockReturnValue( true ),
			get: jest.fn().mockReturnValue( total.toString() ),
		},
	} as any;
};

const createMockResponseWithoutTotal = ( data: any[] ) => {
	return {
		json: jest.fn( () => Promise.resolve( data ) ),
		headers: {
			has: jest.fn().mockReturnValue( false ),
			get: jest.fn().mockReturnValue( null ),
		},
	} as any;
};

describe( 'Venue API', () => {
	const resetModules = () => {
		jest.resetModules();
	};

	const resetMocks = () => {
		jest.resetAllMocks();
	};

	beforeAll( resetModules );
	afterAll( resetModules );
	beforeEach( resetMocks );
	afterEach( resetMocks );

	describe( 'fetchVenues', () => {
		test( 'fetches venues successfully with page parameter', async () => {
			const mockResponse = createMockResponse( [ mockVenueResponse, mockVenueResponse2 ], 2 );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			const result = await fetchVenues( 1 );

			expect( result ).toEqual( mockExpectedFetchResult );
			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				parse: false,
			} );
		} );

		test( 'fetches venues with correct query parameters', async () => {
			const mockResponse = createMockResponse( [ mockVenueResponse ], 1 );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			await fetchVenues( 2 );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( 'page=2' ),
				parse: false,
			} );
		} );

		test( 'handles response without total header', async () => {
			const mockResponse = createMockResponseWithoutTotal( [ mockVenueResponse ] );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			const result = await fetchVenues( 1 );

			expect( result ).toEqual( {
				venues: [ mockMappedVenues[ 0 ] ],
				total: 0,
			} );
		} );

		test( 'rejects when apiFetch throws an error', async () => {
			const apiError = new Error( 'Network error' );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockRejectedValueOnce( apiError );

			await expect( fetchVenues( 1 ) ).rejects.toThrow( 'Failed to fetch venue Network error' );
		} );

		test( 'rejects when response has no json method', async () => {
			const mockResponse = {
				headers: {
					has: jest.fn().mockReturnValue( true ),
					get: jest.fn().mockReturnValue( '2' ),
				},
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			await expect( fetchVenues( 1 ) ).rejects.toEqual( mockResponse );
		} );

		test( 'rejects when response is not an object', async () => {
			const mockResponse = {
				json: jest.fn( () => Promise.resolve( 'not an object' ) ),
				headers: {
					has: jest.fn().mockReturnValue( true ),
					get: jest.fn().mockReturnValue( '2' ),
				},
			} as any;
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			await expect( fetchVenues( 1 ) ).rejects.toThrow(
				'Venues fetch request did not return an object.'
			);
		} );

		test( 'rejects when response is null', async () => {
			const mockResponse = {
				json: jest.fn( () => Promise.resolve( null ) ),
				headers: {
					has: jest.fn().mockReturnValue( true ),
					get: jest.fn().mockReturnValue( '2' ),
				},
			} as any;
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			await expect( fetchVenues( 1 ) ).rejects.toThrow(
				'Venues fetch request did not return an object.'
			);
		} );

		test( 'rejects when response is undefined', async () => {
			const mockResponse = {
				json: jest.fn( () => Promise.resolve( undefined ) ),
				headers: {
					has: jest.fn().mockReturnValue( true ),
					get: jest.fn().mockReturnValue( '2' ),
				},
			} as any;
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			await expect( fetchVenues( 1 ) ).rejects.toThrow(
				'Venues fetch request did not return an object.'
			);
		} );

		test( 'rejects when response is not an array', async () => {
			const mockResponse = {
				json: jest.fn( () => Promise.resolve( { not: 'an array' } ) ),
				headers: {
					has: jest.fn().mockReturnValue( true ),
					get: jest.fn().mockReturnValue( '2' ),
				},
			} as any;
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			await expect( fetchVenues( 1 ) ).rejects.toThrow(
				'Venues fetch request did not return an object.'
			);
		} );

		test( 'handles empty venues array', async () => {
			const mockResponse = createMockResponse( [], 0 );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			const result = await fetchVenues( 1 );

			expect( result ).toEqual( {
				venues: [],
				total: "0",
			} );
		} );

		test( 'maps venue response correctly', async () => {
			const mockResponse = createMockResponse( [ mockVenueResponse ], 1 );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			const result = await fetchVenues( 1 );

			expect( result.venues[ 0 ] ).toEqual( {
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
			} );
		} );

		test( 'maps international venue response correctly', async () => {
			const mockResponse = createMockResponse( [ mockVenueResponseInternational ], 1 );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			const result = await fetchVenues( 1 );

			expect( result.venues[ 0 ] ).toEqual( {
				id: 3,
				venue: 'Sample International Venue',
				address: '789 Maple Street',
				city: 'Toronto',
				country: 'Canada',
				province: 'ON',
				state: '',
				zip: 'M5V 3A8',
				phone: '+1-416-555-0125',
				website: 'https://sample-venue-3.ca',
			} );
		} );
	} );

	describe( 'upsertVenue', () => {
		test( 'creates a new venue successfully', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			const result = await upsertVenue( mockVenueDataForCreate );

			expect( result ).toBe( 1 );
			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: {
					title: 'New Venue',
					status: 'publish',
					address: '789 Pine Street',
					city: 'Chicago',
					country: 'United States',
					state_province: 'IL',
					state: 'IL',
					zip: '60601',
					phone: '+1-312-555-9999',
					website: 'https://new-venue.com',
				},
			} );
		} );

		test( 'updates an existing venue successfully', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			const result = await upsertVenue( mockVenueDataForUpdate );

			expect( result ).toBe( 1 );
			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues/1' ),
				method: 'PUT',
				data: {
					title: 'Updated Venue',
					status: 'publish',
					address: '321 Elm Street',
					city: 'Boston',
					country: 'United States',
					state_province: 'MA',
					state: 'MA',
					zip: '02101',
					phone: '+1-617-555-8888',
					website: 'https://updated-venue.com',
				},
			} );
		} );

		test( 'handles US venue with state field', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( mockVenueDataForCreate );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: expect.objectContaining( {
					state: 'IL',
					state_province: 'IL',
				} ),
			} );
		} );

		test( 'handles international venue with province field', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( mockVenueDataInternational );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: expect.objectContaining( {
					province: 'BC',
					state_province: 'BC',
				} ),
			} );
		} );

		test( 'handles minimal venue data for create', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( mockVenueDataMinimal );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: {
					title: 'Minimal Venue',
					status: 'publish',
					province: '',
				},
			} );
		} );

		test( 'handles venue data with only address', async () => {
			const venueDataWithAddress = {
				...mockVenueDataMinimal,
				address: '123 Test Street',
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( venueDataWithAddress );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: {
					title: 'Minimal Venue',
					status: 'publish',
					address: '123 Test Street',
					province: '',
				},
			} );
		} );

		test( 'handles venue data with only city', async () => {
			const venueDataWithCity = {
				...mockVenueDataMinimal,
				city: 'Test City',
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( venueDataWithCity );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: {
					title: 'Minimal Venue',
					status: 'publish',
					city: 'Test City',
					province: '',
				},
			} );
		} );

		test( 'handles venue data with only country', async () => {
			const venueDataWithCountry = {
				...mockVenueDataMinimal,
				country: 'Test Country',
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( venueDataWithCountry );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: {
					title: 'Minimal Venue',
					status: 'publish',
					country: 'Test Country',
					province: '',
				},
			} );
		} );

		test( 'handles venue data with only phone', async () => {
			const venueDataWithPhone = {
				...mockVenueDataMinimal,
				phone: '+1-555-0000',
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( venueDataWithPhone );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: {
					title: 'Minimal Venue',
					status: 'publish',
					phone: '+1-555-0000',
					province: '',
				},
			} );
		} );

		test( 'handles venue data with only website', async () => {
			const venueDataWithWebsite = {
				...mockVenueDataMinimal,
				website: 'https://example.com',
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( venueDataWithWebsite );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: {
					title: 'Minimal Venue',
					status: 'publish',
					website: 'https://example.com',
					province: '',
				},
			} );
		} );

		test( 'handles venue data with only zip', async () => {
			const venueDataWithZip = {
				...mockVenueDataMinimal,
				zip: '12345',
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( venueDataWithZip );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: {
					title: 'Minimal Venue',
					status: 'publish',
					zip: '12345',
					province: '',
				},
			} );
		} );

		test( 'rejects when apiFetch throws an error during create', async () => {
			const apiError = new Error( 'Network error' );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockRejectedValueOnce( apiError );

			await expect( upsertVenue( mockVenueDataForCreate ) ).rejects.toThrow(
				'Failed to create venue: Network error'
			);
		} );

		test( 'rejects when apiFetch throws an error during update', async () => {
			const apiError = new Error( 'Network error' );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockRejectedValueOnce( apiError );

			await expect( upsertVenue( mockVenueDataForUpdate ) ).rejects.toThrow(
				'Failed to update venue: Network error'
			);
		} );

		test( 'rejects when create response is not an object', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( 'not an object' );

			await expect( upsertVenue( mockVenueDataForCreate ) ).rejects.toThrow(
				'Venue creation request did not return a valid venue object.'
			);
		} );

		test( 'rejects when update response is not an object', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( 'not an object' );

			await expect( upsertVenue( mockVenueDataForUpdate ) ).rejects.toThrow(
				'Venue update request did not return a valid venue object.'
			);
		} );

		test( 'rejects when create response is null', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( null );

			await expect( upsertVenue( mockVenueDataForCreate ) ).rejects.toThrow(
				'Venue creation request did not return a valid venue object.'
			);
		} );

		test( 'rejects when update response is null', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( null );

			await expect( upsertVenue( mockVenueDataForUpdate ) ).rejects.toThrow(
				'Venue update request did not return a valid venue object.'
			);
		} );

		test( 'rejects when create response is undefined', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( undefined );

			await expect( upsertVenue( mockVenueDataForCreate ) ).rejects.toThrow(
				'Venue creation request did not return a valid venue object.'
			);
		} );

		test( 'rejects when update response is undefined', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( undefined );

			await expect( upsertVenue( mockVenueDataForUpdate ) ).rejects.toThrow(
				'Venue update request did not return a valid venue object.'
			);
		} );

		test( 'rejects when create response has no id', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: undefined,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertVenue( mockVenueDataForCreate ) ).rejects.toThrow(
				'Venue creation request did not return a valid venue object.'
			);
		} );

		test( 'rejects when update response has no id', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: undefined,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertVenue( mockVenueDataForUpdate ) ).rejects.toThrow(
				'Venue update request did not return a valid venue object.'
			);
		} );

		test( 'rejects when create response id is null', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: null,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertVenue( mockVenueDataForCreate ) ).rejects.toThrow(
				'Venue creation request did not return a valid venue object.'
			);
		} );

		test( 'rejects when update response id is null', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: null,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertVenue( mockVenueDataForUpdate ) ).rejects.toThrow(
				'Venue update request did not return a valid venue object.'
			);
		} );

		test( 'rejects when create response id is 0', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: 0,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertVenue( mockVenueDataForCreate ) ).rejects.toThrow(
				'Venue creation request did not return a valid venue object.'
			);
		} );

		test( 'rejects when update response id is 0', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: 0,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertVenue( mockVenueDataForUpdate ) ).rejects.toThrow(
				'Venue update request did not return a valid venue object.'
			);
		} );

		test( 'accepts negative id in create response', async () => {
			const responseWithNegativeId = {
				...mockApiResponse,
				id: -1,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( responseWithNegativeId );

			const result = await upsertVenue( mockVenueDataForCreate );

			expect( result ).toBe( -1 );
		} );

		test( 'accepts negative id in update response', async () => {
			const responseWithNegativeId = {
				...mockApiResponse,
				id: -1,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( responseWithNegativeId );

			const result = await upsertVenue( mockVenueDataForUpdate );

			expect( result ).toBe( -1 );
		} );

		test( 'handles venue with id 0 as create operation', async () => {
			const venueDataWithZeroId = {
				...mockVenueDataForCreate,
				id: 0,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( venueDataWithZeroId );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: expect.objectContaining( {
					title: 'New Venue',
					status: 'publish',
				} ),
			} );
		} );

		test( 'handles venue with negative id as create operation', async () => {
			const venueDataWithNegativeId = {
				...mockVenueDataForCreate,
				id: -1,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( venueDataWithNegativeId );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: expect.objectContaining( {
					title: 'New Venue',
					status: 'publish',
				} ),
			} );
		} );

		test( 'handles venue with empty countryCode as non-US', async () => {
			const venueDataWithEmptyCountryCode = {
				...mockVenueDataForCreate,
				countryCode: '',
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( venueDataWithEmptyCountryCode );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: expect.objectContaining( {
					province: 'IL',
					state_province: 'IL',
				} ),
			} );
		} );

		test( 'handles venue with non-US countryCode', async () => {
			const venueDataWithNonUSCountryCode = {
				...mockVenueDataForCreate,
				countryCode: 'CA',
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertVenue( venueDataWithNonUSCountryCode );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/venues' ),
				method: 'POST',
				data: expect.objectContaining( {
					province: 'IL',
					state_province: 'IL',
				} ),
			} );
		} );
	} );
} );
