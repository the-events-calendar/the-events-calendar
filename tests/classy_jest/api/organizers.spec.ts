import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, jest, test } from '@jest/globals';
import apiFetch from '@wordpress/api-fetch';
import { fetchOrganizers, upsertOrganizer } from '@tec/events/classy/api';
import { FetchedOrganizer } from '@tec/events/classy/types/FetchedOrganizer';
import { OrganizerData } from '@tec/events/classy/types/OrganizerData';
import { PostStatus } from '@tec/common/classy/types/Api';

jest.mock( '@wordpress/api-fetch', () => ( {
	__esModule: true,
	default: jest.fn(),
} ) );

// Mock data structures for expected results
const mockOrganizerResponse = {
	id: 1,
	link: 'https://example.com/organizers/sample-organizer-1/',
	title: {
		rendered: 'Sample Organizer 1',
	},
	phone: '+1-555-0123',
	website: 'https://sample-organizer-1.com',
	email: 'contact@sample-organizer-1.com',
	date: '2024-01-01T12:00:00+00:00',
	date_gmt: '2024-01-01T12:00:00Z',
	guid: {
		rendered: 'https://example.com/organizers/sample-organizer-1/',
	},
	modified: '2024-01-01T12:00:00+00:00',
	modified_gmt: '2024-01-01T12:00:00Z',
	slug: 'sample-organizer-1',
	status: 'publish' as PostStatus,
	type: 'tribe_organizer',
	content: {
		rendered: 'This is a sample organizer description.',
		protected: false,
	},
	template: '',
};

const mockOrganizerResponse2 = {
	...mockOrganizerResponse,
	id: 2,
	link: 'https://example.com/organizers/sample-organizer-2/',
	title: {
		rendered: 'Sample Organizer 2',
	},
	phone: '+1-555-0124',
	website: 'https://sample-organizer-2.com',
	email: 'contact@sample-organizer-2.com',
	slug: 'sample-organizer-2',
};

const mockMappedOrganizers: FetchedOrganizer[] = [
	{
		id: 1,
		url: 'https://example.com/organizers/sample-organizer-1/',
		organizer: 'Sample Organizer 1',
		phone: '+1-555-0123',
		email: 'contact@sample-organizer-1.com',
		website: 'https://sample-organizer-1.com',
	},
	{
		id: 2,
		url: 'https://example.com/organizers/sample-organizer-2/',
		organizer: 'Sample Organizer 2',
		phone: '+1-555-0124',
		email: 'contact@sample-organizer-2.com',
		website: 'https://sample-organizer-2.com',
	},
];

const mockOrganizerDataForCreate: OrganizerData = {
	id: null,
	name: 'New Organizer',
	phone: '+1-555-9999',
	website: 'https://new-organizer.com',
	email: 'contact@new-organizer.com',
};

const mockOrganizerDataForUpdate: OrganizerData = {
	id: 1,
	name: 'Updated Organizer',
	phone: '+1-555-8888',
	website: 'https://updated-organizer.com',
	email: 'contact@updated-organizer.com',
};

const mockOrganizerDataMinimal: OrganizerData = {
	id: null,
	name: 'Minimal Organizer',
	phone: '',
	website: '',
	email: '',
};

const mockExpectedFetchResult = {
	organizers: mockMappedOrganizers,
	total: '2',
};

const mockApiResponse = {
	...mockOrganizerResponse,
};

// Mock Response object for fetchOrganizers
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

describe( 'Organizer API', () => {
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

	describe( 'fetchOrganizers', () => {
		test( 'fetches organizers successfully with page parameter', async () => {
			const mockResponse = createMockResponse( [ mockOrganizerResponse, mockOrganizerResponse2 ], 2 );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			const result = await fetchOrganizers( 1 );

			expect( result ).toEqual( mockExpectedFetchResult );
			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/organizers' ),
				parse: false,
			} );
		} );

		test( 'fetches organizers with correct query parameters', async () => {
			const mockResponse = createMockResponse( [ mockOrganizerResponse ], 1 );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			await fetchOrganizers( 2 );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( 'page=2' ),
				parse: false,
			} );
		} );

		test( 'handles response without total header', async () => {
			const mockResponse = createMockResponseWithoutTotal( [ mockOrganizerResponse ] );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			const result = await fetchOrganizers( 1 );

			expect( result ).toEqual( {
				organizers: [ mockMappedOrganizers[ 0 ] ],
				total: 0,
			} );
		} );

		test( 'rejects when apiFetch throws an error', async () => {
			const apiError = new Error( 'Network error' );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockRejectedValueOnce( apiError );

			await expect( fetchOrganizers( 1 ) ).rejects.toThrow( 'Failed to fetch organizer Network error' );
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

			await expect( fetchOrganizers( 1 ) ).rejects.toEqual( mockResponse );
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

			await expect( fetchOrganizers( 1 ) ).rejects.toThrow(
				'Organizers fetch request did not return an object.'
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

			await expect( fetchOrganizers( 1 ) ).rejects.toThrow(
				'Organizers fetch request did not return an object.'
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

			await expect( fetchOrganizers( 1 ) ).rejects.toThrow(
				'Organizers fetch request did not return an object.'
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

			await expect( fetchOrganizers( 1 ) ).rejects.toThrow(
				'Organizers fetch request did not return an object.'
			);
		} );

		test( 'handles empty organizers array', async () => {
			const mockResponse = createMockResponse( [], 0 );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			const result = await fetchOrganizers( 1 );

			expect( result ).toEqual( {
				organizers: [],
				total: '0',
			} );
		} );

		test( 'maps organizer response correctly', async () => {
			const mockResponse = createMockResponse( [ mockOrganizerResponse ], 1 );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockResponse );

			const result = await fetchOrganizers( 1 );

			expect( result.organizers[ 0 ] ).toEqual( {
				id: 1,
				url: 'https://example.com/organizers/sample-organizer-1/',
				organizer: 'Sample Organizer 1',
				phone: '+1-555-0123',
				email: 'contact@sample-organizer-1.com',
				website: 'https://sample-organizer-1.com',
			} );
		} );
	} );

	describe( 'upsertOrganizer', () => {
		test( 'creates a new organizer successfully', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			const result = await upsertOrganizer( mockOrganizerDataForCreate );

			expect( result ).toBe( 1 );
			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/organizers' ),
				method: 'POST',
				data: {
					title: 'New Organizer',
					status: 'publish',
					phone: '+1-555-9999',
					email: 'contact@new-organizer.com',
					website: 'https://new-organizer.com',
				},
			} );
		} );

		test( 'updates an existing organizer successfully', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			const result = await upsertOrganizer( mockOrganizerDataForUpdate );

			expect( result ).toBe( 1 );
			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/organizers/1' ),
				method: 'PUT',
				data: {
					title: 'Updated Organizer',
					status: 'publish',
					phone: '+1-555-8888',
					email: 'contact@updated-organizer.com',
					website: 'https://updated-organizer.com',
				},
			} );
		} );

		test( 'handles minimal organizer data for create', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertOrganizer( mockOrganizerDataMinimal );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/organizers' ),
				method: 'POST',
				data: {
					title: 'Minimal Organizer',
					status: 'publish',
				},
			} );
		} );

		test( 'handles organizer data with only phone', async () => {
			const organizerDataWithPhone = {
				...mockOrganizerDataMinimal,
				phone: '+1-555-0000',
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertOrganizer( organizerDataWithPhone );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/organizers' ),
				method: 'POST',
				data: {
					title: 'Minimal Organizer',
					status: 'publish',
					phone: '+1-555-0000',
				},
			} );
		} );

		test( 'handles organizer data with only email', async () => {
			const organizerDataWithEmail = {
				...mockOrganizerDataMinimal,
				email: 'test@example.com',
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertOrganizer( organizerDataWithEmail );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/organizers' ),
				method: 'POST',
				data: {
					title: 'Minimal Organizer',
					status: 'publish',
					email: 'test@example.com',
				},
			} );
		} );

		test( 'handles organizer data with only website', async () => {
			const organizerDataWithWebsite = {
				...mockOrganizerDataMinimal,
				website: 'https://example.com',
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertOrganizer( organizerDataWithWebsite );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/organizers' ),
				method: 'POST',
				data: {
					title: 'Minimal Organizer',
					status: 'publish',
					website: 'https://example.com',
				},
			} );
		} );

		test( 'rejects when apiFetch throws an error during create', async () => {
			const apiError = new Error( 'Network error' );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockRejectedValueOnce( apiError );

			await expect( upsertOrganizer( mockOrganizerDataForCreate ) ).rejects.toThrow(
				'Failed to create organizer: Network error'
			);
		} );

		test( 'rejects when apiFetch throws an error during update', async () => {
			const apiError = new Error( 'Network error' );
			// @ts-ignore
			( apiFetch as jest.Mock ).mockRejectedValueOnce( apiError );

			await expect( upsertOrganizer( mockOrganizerDataForUpdate ) ).rejects.toThrow(
				'Failed to update organizer: Network error'
			);
		} );

		test( 'rejects when create response is not an object', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( 'not an object' );

			await expect( upsertOrganizer( mockOrganizerDataForCreate ) ).rejects.toThrow(
				'Organizer creation request did not return a valid organizer object.'
			);
		} );

		test( 'rejects when update response is not an object', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( 'not an object' );

			await expect( upsertOrganizer( mockOrganizerDataForUpdate ) ).rejects.toThrow(
				'Organizer update request did not return a valid organizer object.'
			);
		} );

		test( 'rejects when create response is null', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( null );

			await expect( upsertOrganizer( mockOrganizerDataForCreate ) ).rejects.toThrow(
				'Organizer creation request did not return a valid organizer object.'
			);
		} );

		test( 'rejects when update response is null', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( null );

			await expect( upsertOrganizer( mockOrganizerDataForUpdate ) ).rejects.toThrow(
				'Organizer update request did not return a valid organizer object.'
			);
		} );

		test( 'rejects when create response is undefined', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( undefined );

			await expect( upsertOrganizer( mockOrganizerDataForCreate ) ).rejects.toThrow(
				'Organizer creation request did not return a valid organizer object.'
			);
		} );

		test( 'rejects when update response is undefined', async () => {
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( undefined );

			await expect( upsertOrganizer( mockOrganizerDataForUpdate ) ).rejects.toThrow(
				'Organizer update request did not return a valid organizer object.'
			);
		} );

		test( 'rejects when create response has no id', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: undefined,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertOrganizer( mockOrganizerDataForCreate ) ).rejects.toThrow(
				'Organizer creation request did not return a valid organizer object.'
			);
		} );

		test( 'rejects when update response has no id', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: undefined,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertOrganizer( mockOrganizerDataForUpdate ) ).rejects.toThrow(
				'Organizer update request did not return a valid organizer object.'
			);
		} );

		test( 'rejects when create response id is null', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: null,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertOrganizer( mockOrganizerDataForCreate ) ).rejects.toThrow(
				'Organizer creation request did not return a valid organizer object.'
			);
		} );

		test( 'rejects when update response id is null', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: null,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertOrganizer( mockOrganizerDataForUpdate ) ).rejects.toThrow(
				'Organizer update request did not return a valid organizer object.'
			);
		} );

		test( 'rejects when create response id is 0', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: 0,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertOrganizer( mockOrganizerDataForCreate ) ).rejects.toThrow(
				'Organizer creation request did not return a valid organizer object.'
			);
		} );

		test( 'rejects when update response id is 0', async () => {
			const invalidResponse = {
				...mockApiResponse,
				id: 0,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( invalidResponse );

			await expect( upsertOrganizer( mockOrganizerDataForUpdate ) ).rejects.toThrow(
				'Organizer update request did not return a valid organizer object.'
			);
		} );

		test( 'accepts negative id in create response', async () => {
			const responseWithNegativeId = {
				...mockApiResponse,
				id: -1,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( responseWithNegativeId );

			const result = await upsertOrganizer( mockOrganizerDataForCreate );

			expect( result ).toBe( -1 );
		} );

		test( 'accepts negative id in update response', async () => {
			const responseWithNegativeId = {
				...mockApiResponse,
				id: -1,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( responseWithNegativeId );

			const result = await upsertOrganizer( mockOrganizerDataForUpdate );

			expect( result ).toBe( -1 );
		} );

		test( 'handles organizer with id 0 as create operation', async () => {
			const organizerDataWithZeroId = {
				...mockOrganizerDataForCreate,
				id: 0,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertOrganizer( organizerDataWithZeroId );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/organizers' ),
				method: 'POST',
				data: expect.objectContaining( {
					title: 'New Organizer',
					status: 'publish',
				} ),
			} );
		} );

		test( 'handles organizer with negative id as create operation', async () => {
			const organizerDataWithNegativeId = {
				...mockOrganizerDataForCreate,
				id: -1,
			};
			// @ts-ignore
			( apiFetch as jest.Mock ).mockResolvedValueOnce( mockApiResponse );

			await upsertOrganizer( organizerDataWithNegativeId );

			expect( apiFetch ).toHaveBeenCalledWith( {
				path: expect.stringContaining( '/organizers' ),
				method: 'POST',
				data: expect.objectContaining( {
					title: 'New Organizer',
					status: 'publish',
				} ),
			} );
		} );
	} );
} );
