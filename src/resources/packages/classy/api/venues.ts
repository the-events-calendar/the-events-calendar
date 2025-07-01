import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { FetchedVenue } from '../types/FetchedVenue';

/**
 * Result of a venue fetch operation.
 */
export type VenuesFetchResult = {
	venues: FetchedVenue[];
	total: number;
};

/**
 * Fetches venues from the REST API.
 *
 * The function will throw an Error if the fetch request fails: this is by design. It's up to the client
 * code using the function to catch and react to the error.
 * The function implicitly relies on the pagination set for the Venue post type in the REST API context.
 *
 * @param {number} page The page number to fetch.
 *
 * @returns {Promise<VenuesFetchResult>} A promise that resolves to an object containing venues array and total count.
 *
 * @throws {Error} Will throw an error if the fetch request fails.
 */
export const fetchVenues = async ( page: number ): Promise< VenuesFetchResult > => {
	const results = await apiFetch( {
		path: addQueryArgs( '/tribe/events/v1/venues', {
			page,
		} ),
	} );

	// Check that the results have been returned in an object.
	if ( ! ( results && typeof results === 'object' ) ) {
		throw new Error( 'Venues fetch request did not return an object.' );
	}

	// Check that the object has a 'venues' property.
	if ( ! ( results.hasOwnProperty( 'venues' ) && results.hasOwnProperty( 'total' ) ) ) {
		throw new Error( 'Venues fetch request did not return an object with venues and total properties.' );
	}

	// Check that the `venues` property is an array.
	if ( ! Array.isArray( ( results as { venues: any } ).venues ) ) {
		throw new Error( 'Venues fetch request did not return an array.' );
	}

	const safeResults = results as {
		venues: FetchedVenue[];
		total: number;
	};

	return {
		venues: safeResults.venues,
		total: safeResults.total,
	};
};
