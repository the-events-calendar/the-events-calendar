import { addQueryArgs } from '@wordpress/url';
import { FetchedVenue } from '../types/FetchedVenue';
import { VenueData } from '../types/VenueData';
import { getTecApiUrl } from '@tec/common/classy/api';
import { PostStatus } from '@tec/common/classy/types/Api';
import apiFetch from '@wordpress/api-fetch';

const baseRoute = getTecApiUrl( '/venues' );

/**
 * Formatted result of fetching venues, including the array of venues and the total count.
 */
export type VenuesFetchResult = {
	venues: FetchedVenue[];
	total: number;
};

/**
 * The structure of a venue as returned by the REST API.
 */
type VenueResponse = {
	// These are the properties we care about.
	id: number;
	link: string;
	title: {
		rendered: string;
	};
	address: string;
	city: string;
	country: string;
	state_province: string;
	state: string;
	province: string;
	zip: string;
	phone: string;
	website: string;
	directions_link: string;

	// The rest of the properties are included for completeness, but not used in the mapping.
	date: string;
	date_gmt: string;
	guid: {
		rendered: string;
	};
	modified: string;
	modified_gmt: string;
	slug: string;
	status: PostStatus;
	type: string;
	content: {
		rendered: string;
		protected: boolean;
	};
	template: string;
};

type VenueUpsertRequest = {
	title: string;
	status: PostStatus;
	content?: string;
	address?: string;
	city?: string;
	country?: string;
	state_province?: string;
	state?: string;
	province?: string;
	zip?: string;
	phone?: string;
	website?: string;
};

/**
 * Fetches venues from the REST API.
 *
 * The function will throw an Error if the fetch request fails: this is by design. It's up to the client
 * code using the function to catch and react to the error.
 * The function implicitly relies on the pagination set for the Venue post type in the REST API context.
 *
 * @since TBD
 *
 * @param {number} page The page number to fetch.
 *
 * @returns {Promise<VenuesFetchResult>} A promise that resolves to an object containing venues array and total count.
 *
 * @throws {Error} Will throw an error if the fetch request fails.
 */
export const fetchVenues = async ( page: number ): Promise< VenuesFetchResult > => {
	return new Promise< VenuesFetchResult >( async ( resolve, reject ): Promise< void > => {
		await apiFetch( {
			path: addQueryArgs( baseRoute, { page } ),
			parse: false,
		} )
			.then( async ( response: Response ) => {
				if ( ! response.json ) {
					reject( response );
				}

				const venues = await response.json();

				// Check that the results have been returned in an object.
				if ( ! ( venues && typeof venues === 'object' ) ) {
					reject( new Error( 'Venues fetch request did not return an object.' ) );
				}

				// Check that the `venues` property is an array.
				if ( ! Array.isArray( venues ) ) {
					reject( new Error( 'Venues fetch request did not return an object.' ) );
				}

				const total = response.headers.has( 'x-wp-total' ) ? response.headers.get( 'x-wp-total' ) : 0;

				resolve( {
					venues: venues.map( mapVenueResponse ),
					total: total,
				} as VenuesFetchResult );
			} )
			.catch( ( error ) => {
				reject( new Error( `Failed to fetch venue ${ error.message }` ) );
			} );
	} );
};

/**
 * Upserts a venue by either updating an existing one or creating a new one based on the provided data.
 *
 * The function will throw an Error if the fetch request fails: this is by design. It's up to the client
 * code using the function to catch and react to the error.
 *
 * @since TBD
 *
 * @param {VenueData} venueData The data of the venue to be updated or inserted.
 *
 * @return {Promise<number>} A promise that resolves to the ID of the updated or inserted venue.
 *
 * @throws {Error} Will throw an error if the fetch request fails.
 */
export const upsertVenue = async ( venueData: VenueData ): Promise< number > => {
	const isUpdate = Boolean( venueData.id && venueData.id > 0 );
	const isCountryUs = venueData.countryCode === 'US';
	const upsertParams: VenueUpsertRequest = {
		title: venueData.name,
		status: 'publish' as PostStatus,
		address: venueData.address,
		city: venueData.city,
		country: venueData.country,
		state_province: venueData.stateprovince,
		state: isCountryUs ? venueData.stateprovince : '',
		province: isCountryUs ? '' : venueData.stateprovince,
		zip: venueData.zip,
		phone: venueData.phone,
		website: venueData.website,
	};

	return new Promise< number >( async ( resolve, reject ): Promise< void > => {
		await apiFetch( {
			path: `${ baseRoute }${ isUpdate ? `/${ venueData.id }` : '' }`,
			method: isUpdate ? 'PUT' : 'POST',
			data: upsertParams,
		} )
			.then( ( response: VenueResponse ) => {
				if ( ! response || typeof response !== 'object' || ! response.id ) {
					reject(
						new Error(
							`Venue ${ isUpdate ? 'update' : 'creation' } request did not return a valid venue object.`
						)
					);
				} else {
					resolve( response.id );
				}
			} )
			.catch( ( error ) => {
				reject( new Error( `Failed to ${ isUpdate ? 'update' : 'create' } venue: ${ error.message }` ) );
			} );
	} );
};

/**
 * Maps a venue response from the API to the FetchedVenue type.
 *
 * @since TBD
 *
 * @param {VenueResponse} venue The venue data from the API response.
 * @return {FetchedVenue} The mapped venue data.
 */
const mapVenueResponse = ( venue: VenueResponse ): FetchedVenue => {
	return {
		id: venue.id,
		venue: venue.title.rendered,
		address: venue.address,
		city: venue.city,
		country: venue.country,
		province: venue.province,
		state: venue.state,
		zip: venue.zip,
		phone: venue.phone,
		website: venue.website,
	};
};
