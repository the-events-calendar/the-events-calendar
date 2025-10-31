import { addQueryArgs } from '@wordpress/url';
import { FetchedOrganizer } from '../types/FetchedOrganizer';
import { OrganizerData } from '../types/OrganizerData';
import { getTecApiUrl } from '@tec/common/tecApi';
import { PostStatus } from '@tec/common/classy/types/Api';
import apiFetch from '@wordpress/api-fetch';

const baseRoute = getTecApiUrl( '/organizers' );

/**
 * Formatted result of fetching organizers, including the array of organizers and the total count.
 */
export type OrganizersFetchResult = {
	organizers: FetchedOrganizer[];
	total: number;
};

/**
 * The structure of an organizer as returned by the REST API.
 */
type OrganizerResponse = {
	// These are the properties we care about.
	id: number;
	link: string;
	title: {
		rendered: string;
	};
	phone: string;
	website: string;
	email: string;

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

type OrganizerUpsertRequest = {
	title: string;
	status: PostStatus;
	content?: string;
	phone?: string;
	email?: string;
	website?: string;
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
	return new Promise< OrganizersFetchResult >( async ( resolve, reject ): Promise< void > => {
		await apiFetch( {
			path: addQueryArgs( baseRoute, { page } ),
			parse: false,
		} )
			.then( async ( response: Response ) => {
				if ( ! response.json ) {
					reject( response );
				}

				const organizers = await response.json();

				// Check that the results have been returned in an object.
				if ( ! ( organizers && typeof organizers === 'object' ) ) {
					reject( new Error( 'Organizers fetch request did not return an object.' ) );
				}

				// Check that the `organizers` property is an array.
				if ( ! Array.isArray( organizers ) ) {
					reject( new Error( 'Organizers fetch request did not return an object.' ) );
				}

				const total = response.headers.has( 'x-wp-total' ) ? response.headers.get( 'x-wp-total' ) : 0;

				resolve( {
					organizers: organizers.map( mapOrganizerResponse ),
					total: total,
				} as OrganizersFetchResult );
			} )
			.catch( ( error ) => {
				reject( new Error( `Failed to fetch organizer ${ error.message }` ) );
			} );
	} );
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
	const isUpdate = Boolean( organizerData.id && organizerData.id > 0 );
	const upsertParams: OrganizerUpsertRequest = {
		title: organizerData.name,
		status: 'publish' as PostStatus,
		...( organizerData.phone ? { phone: organizerData.phone } : {} ),
		...( organizerData.email ? { email: organizerData.email } : {} ),
		...( organizerData.website ? { website: organizerData.website } : {} ),
	};

	return new Promise< number >( async ( resolve, reject ): Promise< void > => {
		await apiFetch( {
			path: `${ baseRoute }${ isUpdate ? `/${ organizerData.id }` : '' }`,
			method: isUpdate ? 'PUT' : 'POST',
			data: upsertParams,
		} )
			.then( ( response: OrganizerResponse ) => {
				if ( ! response || typeof response !== 'object' || ! response.id ) {
					reject(
						new Error(
							`Organizer ${
								isUpdate ? 'update' : 'creation'
							} request did not return a valid organizer object.`
						)
					);
				} else {
					resolve( response.id );
				}
			} )
			.catch( ( error ) => {
				reject( new Error( `Failed to ${ isUpdate ? 'update' : 'create' } organizer: ${ error.message }` ) );
			} );
	} );
};

/**
 * Maps an organizer response from the API to the FetchedOrganizer type.
 *
 * @since TBD
 *
 * @param {OrganizerResponse} organizer The organizer data from the API response.
 * @return {FetchedOrganizer} The mapped organizer data.
 */
const mapOrganizerResponse = ( organizer: OrganizerResponse ): FetchedOrganizer => {
	return {
		id: organizer.id,
		url: organizer.link,
		organizer: organizer.title.rendered,
		phone: organizer.phone,
		email: organizer.email,
		website: organizer.website,
	};
};
