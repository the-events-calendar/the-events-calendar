import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { FetchedOrganizer } from '../types/FetchedOrganizer';
import { OrganizerData } from '../types/OrganizerData';

/**
 * Result of an organizer fetch operation.
 */
export type OrganizersFetchResult = {
	organizers: FetchedOrganizer[];
	total: number;
};

/**
 * Fetches organizers from the REST API.
 *
 * The function will throw an Error if the fetch request fails: this is by design. It's up to the client
 * code using the function to catch and react to the error.
 * The function implicitly relies on the pagination set for the Organizer post type in the REST API context.
 *
 * @since TBD
 *
 * @param {number} page The page number to fetch.
 *
 * @returns {Promise<OrganizersFetchResult>} A promise that resolves to an object containing organizers array and total count.
 *
 * @throws {Error} Will throw an error if the fetch request fails.
 */
export const fetchOrganizers = async ( page: number ): Promise< OrganizersFetchResult > => {
	const results = await apiFetch( {
		path: addQueryArgs( '/tribe/events/v1/organizers', {
			page,
		} ),
	} );

	// Check that the results have been returned in an object.
	if ( ! ( results && typeof results === 'object' ) ) {
		throw new Error( 'Organizers fetch request did not return an object.' );
	}

	// Check that the object has an 'organizers' property.
	if ( ! ( results.hasOwnProperty( 'organizers' ) && results.hasOwnProperty( 'total' ) ) ) {
		throw new Error( 'Organizers fetch request did not return an object with organizers and total properties.' );
	}

	// Check that the `organizers` property is an array.
	if ( ! Array.isArray( ( results as { organizers: any } ).organizers ) ) {
		throw new Error( 'Organizers fetch request did not return an array.' );
	}

	const safeResults = results as {
		organizers: FetchedOrganizer[];
		total: number;
	};

	return {
		organizers: safeResults.organizers,
		total: safeResults.total,
	};
};

/**
 * Upserts an organizer by either updating an existing one or creating a new one based on the provided data.
 *
 * The function will throw an Error if the fetch request fails: this is by design. It's up to the client
 * code using the function to catch and react to the error.
 *
 * @since TBD
 *
 * @param {OrganizerData} organizerData The data of the organizer to be updated or inserted.
 *
 * @return {Promise<number>} A promise that resolves to the ID of the updated or inserted organizer.
 *
 * @throws {Error} Will throw an error if the fetch request fails.
 */
export const upsertOrganizer = async ( organizerData: OrganizerData ): Promise< number > => {
	let fetchPromise: Promise< FetchedOrganizer >;

	if ( organizerData.id ) {
		// Updating an existing organizer.
		fetchPromise = apiFetch( {
			path: `/tribe/events/v1/organizers/${ organizerData.id }`,
			method: 'PUT',
			data: {
				organizer: organizerData.name,
				phone: organizerData.phone,
				email: organizerData.email,
				website: organizerData.website,
			},
		} );
	} else {
		// Creating a new organizer.
		fetchPromise = apiFetch( {
			path: '/tribe/events/v1/organizers',
			method: 'POST',
			data: {
				organizer: organizerData.name,
				phone: organizerData.phone,
				email: organizerData.email,
				website: organizerData.website,
			},
		} );
	}

	const data = await fetchPromise;
	return data.id;
};
