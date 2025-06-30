import * as React from 'react';
import { Fragment, MouseEventHandler, useCallback, useEffect, useRef, useState } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { Button, CustomSelectControl, Slot } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { _x } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import { IconAdd } from '@tec/common/classy/components';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import VenueCards from './VenueCards';
import { CustomSelectOption } from '@wordpress/components/build-types/custom-select-control/types';
import { sortOptionsForDisplay } from '@tec/common/classy/functions/sortOptionsForDisplay';
import { FetchedVenue } from '../../types/FetchedVenue';
import { METADATA_EVENT_VENUE_ID } from '../../constants';
import { VenueData } from '../../types/VenueData';
import VenueUpsertModal from './VenueUpsertModal.tsx';

function buildOptionFromFetchedVenue( venue: FetchedVenue ): CustomSelectOption {
	return {
		key: venue.id.toString(),
		name: venue.venue,
		value: venue.id.toString(),
		label: venue.venue,
	};
}

const placeholderOption: CustomSelectOption = {
	key: '0',
	name: _x( 'Select venue', 'Venue selection placeholder option', 'the-events-calendar' ),
	value: '0',
};

function getUpdatedOptions( venues: FetchedVenue[], currentVenueIds: number[] ) {
	return venues
		.filter( ( venue ) => ! currentVenueIds.includes( venue.id ) )
		.map( buildOptionFromFetchedVenue )
		.sort( sortOptionsForDisplay );
}

export default function EventLocation( props: FieldProps ) {
	// Initially set the options to an array that only contains the placeholder.
	const [ options, setOptions ] = useState( [ placeholderOption ] );

	const { meta, venuesLimit } = useSelect(
		(
			select
		): {
			meta: Object;
			venuesLimit: number;
		} => {
			const editorStore: {
				getEditedPostAttribute: ( attribute: string ) => any;
			} = select( 'core/editor' );
			const classyStore: {
				getVenuesLimit: () => number;
			} = select( 'tec/classy/events' );

			const meta = editorStore.getEditedPostAttribute( 'meta' ) || null;
			const venuesLimit = classyStore.getVenuesLimit() || 1;

			return {
				meta,
				venuesLimit,
			};
		},
		[]
	);

	const venueIds = meta?.[ METADATA_EVENT_VENUE_ID ]?.map( ( id: string ): number => parseInt( id, 10 ) ) || [];

	const { editPost } = useDispatch( 'core/editor' );

	const [ currentVenueIds, setCurrentVenueIds ] = useState( venueIds );

	// To start, the page to fetch is the first one.
	const [ pageToFetch, setPageToFetch ] = useState( 1 );

	// If the initial number of venues is 0, we are adding a new venue.
	const [ isAdding, setIsAdding ] = useState( false );

	// Whether the user is currently inserting or updating a venue or not.
	const [ isUpserting, setIsUpserting ] = useState< number | false >( false );

	const getVenueData = useCallback( (): VenueData => {
		const id = isUpserting || null;
		const venue = id ? fetched.current.find( ( venue: FetchedVenue ) => venue.id === id ) : null;

		// Editing an existing venue.
		if ( venue ) {
			return {
				id: id,
				name: venue.venue,
				address: venue.address,
				city: venue.city,
				country: venue.country,
				countryCode: '',
				province: venue.province,
				stateprovince: venue.state,
				zip: venue.zip,
				phone: venue.phone,
				website: venue.website,
			};
		}

		// Creating a new venue or, somehow, trying to edit a non-yet fetched venue.
		return {
			id: id,
			name: '',
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
	}, [ pageToFetch, isUpserting ] );

	// Keep a set of fetched Venue objects across renders.
	const fetched = useRef( [] as FetchedVenue[] );

	useEffect( () => {
		apiFetch( {
			path: addQueryArgs( '/tribe/events/v1/venues', {
				page: pageToFetch,
			} ),
		} )
			.then( ( results ) => {
				// Check that results is an object, else log a console error.
				if ( ! ( results && typeof results === 'object' ) ) {
					console.error( 'Venues fetch request did not return an object.' );
					return;
				}

				// Check that the object has an `venues` property, else log a console error.
				if ( ! ( results.hasOwnProperty( 'venues' ) && results.hasOwnProperty( 'total' ) ) ) {
					console.error( 'Venues fetch request did not return an object with venues and total properties.' );
					return;
				}

				// Check that the `venues` property is an array, else log a console error.
				if ( ! Array.isArray( ( results as { venues: any } ).venues ) ) {
					console.error( 'Venues fetch request did not return an array.' );
					return;
				}

				// Check that the venue array is not empty, else return.
				if ( ( results as { venues: any[] } ).venues.length === 0 ) {
					return;
				}

				const safeResults = results as {
					venues: FetchedVenue[];
					total: number;
				};

				// Update the number of pages to fetch if the total is more than the number of fetched options.
				if ( safeResults.total > fetched.current.length + safeResults.venues.length ) {
					setPageToFetch( pageToFetch + 1 );
				}

				// Update the fetched set of venues by making sure a new version of venues will override the already
				// fetched version.
				const safeResultIds = new Set( safeResults.venues.map( ( venue: FetchedVenue ): number => venue.id ) );
				fetched.current = [
					...fetched.current.filter( ( venue ) => ! safeResultIds.has( venue.id ) ),
					...safeResults.venues,
				];

				// Update the options to all the so-far fetched options minus the current venue ids.
				// Why not just add to the options? They might have been modified by a user removal or selection in the
				// meanwhile. Since we're recalculating them anyway, make sure they are up to date.
				setOptions( getUpdatedOptions( fetched.current, currentVenueIds ) );
			} )
			.catch( ( e ) => {
				console.error( 'Venue fetch request failed: ' + e.message );
			} );
	}, [ pageToFetch ] );

	const ref = useRef( null );

	function onVenueEdit( id: number ): void {
		setIsUpserting( id );
	}

	const onVenueSelect = useCallback(
		( newValue: { selectedItem: CustomSelectOption } ) => {
			const venueIds = [ ...currentVenueIds, parseInt( newValue.selectedItem.key ) ];
			setCurrentVenueIds( venueIds );
			setOptions( getUpdatedOptions( fetched.current, venueIds ) );

			setIsAdding( false );

			editPost( {
				meta: { [ METADATA_EVENT_VENUE_ID ]: venueIds },
			} );
		},
		[ currentVenueIds ]
	);

	const onVenueRemove = useCallback(
		( id: number ): void => {
			const venueIds = currentVenueIds.filter( ( venueId ) => venueId !== id );
			setCurrentVenueIds( venueIds );

			setOptions( getUpdatedOptions( fetched.current, venueIds ) );

			editPost( {
				meta: { [ METADATA_EVENT_VENUE_ID ]: venueIds },
			} );
		},
		[ currentVenueIds ]
	);

	const createNewVenue: MouseEventHandler = () => {
		// We're creating a new venue, the ID is 0.
		setIsUpserting( 0 );
	};

	/**
	 * Upserts a venue by either updating an existing one or creating a new one based on the provided data.
	 *
	 * @since TBD
	 *
	 * @param {VenueData} venueData The data of the venue to be updated or inserted.
	 *
	 * @return {Promise<void>} A promise that resolves when the REST API replies.
	 */
	const upsertVenue = useCallback( ( venueData: VenueData ) => {
		let fetchPromise: Promise< FetchedVenue >;
		const isCountryUs = venueData.countryCode === 'US';

		if ( venueData.id ) {
			// Updating an existing venue.
			fetchPromise = apiFetch( {
				path: `/tribe/events/v1/venues/${ venueData.id }`,
				method: 'PUT',
				data: {
					venue: venueData.name,
					address: venueData.address,
					city: venueData.city,
					country: venueData.country,
					province: isCountryUs ? '' : venueData.stateprovince,
					state: isCountryUs ? venueData.stateprovince : '',
					zip: venueData.zip,
					phone: venueData.phone,
					website: venueData.website,
				},
			} );
		} else {
			// Creating a new venue.
			fetchPromise = apiFetch( {
				path: '/tribe/events/v1/venues',
				method: 'POST',
				data: {
					status: 'publish',
					venue: venueData.name,
					address: venueData.address,
					city: venueData.city,
					country: venueData.country,
					province: isCountryUs ? '' : venueData.stateprovince,
					state: isCountryUs ? venueData.stateprovince : '',
					zip: venueData.zip,
					phone: venueData.phone,
					website: venueData.website,
				},
			} );
		}

		fetchPromise
			.then( ( data: FetchedVenue ) => {
				setIsUpserting( false );

				const index: number = fetched.current.findIndex( ( venue ) => venue.id === data.id );

				if ( index === -1 ) {
					// A new venue has been created: add it to the fetched set of venues.
					fetched.current.push( data );

					// Add the venue ID to the list of current venue IDs: we assume the user created the Venue to add
					// it.
					const newCurrentVenueIds = [ ...currentVenueIds, data.id ];
					setCurrentVenueIds( newCurrentVenueIds );

					// Update the post meta to track the new venues IDs.
					editPost( {
						meta: {
							[ METADATA_EVENT_VENUE_ID ]: newCurrentVenueIds,
						},
					} );

					// Update the options to the new set of venues; this will trigger a re-render.
					setOptions( getUpdatedOptions( fetched.current, newCurrentVenueIds ) );
				} else {
					// A venue has been updated: update it in the set of fetched venues.
					fetched.current[ index ] = data;
					// Update the options to the new set; this will trigger a re-render.
					setOptions( getUpdatedOptions( fetched.current, currentVenueIds ) );
				}
			} )
			.catch( ( error ) => {
				setIsUpserting( false );
				// Set the page to fetch to 0 to make sure all the Venues will be re-fetched.
				setPageToFetch( 0 );
				console.error( 'Venue upsert request failed: ' + error.message );
			} );
	}, [] );

	const orderedVenues = currentVenueIds
		.map( ( id ) => fetched.current.find( ( venue ) => venue.id === id ) )
		.filter( ( venue ) => venue !== undefined );

	return (
		<div className="classy-field classy-field--event-location">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs classy-field__inputs--boxed">
				{ currentVenueIds.length > 0 && (
					<div className="classy-field__inputs-section">
						<VenueCards venues={ orderedVenues } onEdit={ onVenueEdit } onRemove={ onVenueRemove } />
					</div>
				) }

				{ isAdding && currentVenueIds.length > 0 && <span className="classy_section-separator"></span> }

				{currentVenueIds.length < venuesLimit &&
					<div
						className="classy-field__inputs-section classy-field__inputs-section--row classy-field__inputs-section--justify-left"
					>
						{ ( isAdding || currentVenueIds.length === 0 ) && (
							<Fragment>
								<div className="classy-field__input classy-field__input-full-width">
									<CustomSelectControl
										__next40pxDefaultSize
										className="classy-field__control classy-field__control--select"
										hideLabelFromVision={ true }
										label={ _x(
											'Venue selection',
											'Assistive technology label',
											'the-events-calendar'
										) }
										onChange={ onVenueSelect }
										options={ options }
										value={ placeholderOption }
									/>
								</div>

								<div className="classy-field__input" ref={ ref }>
									<div className="classy-field__control classy-field__control--venue" ref={ ref }>
									<span className="classy-field__venue-label">
										{ _x(
											'or',
											'prefix to the Venue create popover link ',
											'the-events-calendar'
										) }
									</span>{ ' ' }
										<Button
											variant="link"
											className="classy-cta classy-field__venue-value"
											onClick={ createNewVenue }
										>
											{ _x(
												'Create new venue',
												'Call to action to create a new venue',
												'the-events-calendar'
											) }
										</Button>
									</div>
								</div>
							</Fragment>
						) }

						{ ! isAdding && currentVenueIds.length > 0 && currentVenueIds.length < venuesLimit && (
							<div className="classy-field__input">
								<Button
									variant="link"
									className="classy-field__control classy-field__control--cta"
									onClick={ () => setIsAdding( true ) }
								>
									<IconAdd />
									{ _x(
										'Add another venue',
										'Call-to-action to add another venue',
										'the-events-calendar'
									) }
								</Button>
							</div>
						) }
					</div>
				}

				{ currentVenueIds.length > 0 && isAdding && (
					<div className="classy-field__inputs-section classy-field__inputs-section--row">
						<Button
							variant="link"
							className="classy-field__control classy-field__control--cancel"
							onClick={ () => setIsAdding( false ) }
						>
							{ _x( 'Cancel', 'Cancel the venue selection', 'the-events-calendar' ) }
						</Button>
					</div>
				) }

				{ isUpserting !== false && (
					<VenueUpsertModal
						isUpdate={ isUpserting > 0 }
						onCancel={ () => setIsUpserting( false ) }
						onSave={ upsertVenue }
						onClose={ () => setIsUpserting( false ) }
						values={ getVenueData() }
					/>
				) }

				{ /**
				 * Renders at the end of the Event Location field of The Events Calendar.
				 *
				 * Extending plugins must hook on the `tec.classy.render` filter and render components in this Slot.
				 *
				 * Example:
				 * ```
				 * addFilter(
				 * 	'tec.classy.render',
				 * 	'tec.classy.my-plugin',
				 * 	(fields: React.ReactNode | null) => (
				 * 		<Fragment>
				 * 			{fields}
				 * 			<Fill name='tec.classy.events.event-location.after'>
				 * 				<p>HELLO FROM MY PLUGIN</p>
				 * 			</Fill>
				 * 		</Fragment>
				 * 	)
				 * );
				 * ```
				 *
				 * Note that, as in any filter, it's up to the extending plugin to manage priority in the filter
				 * or whether previous nodes will be rendered or not.
				 */ }
				<Slot name="tec.classy.events.event-location.after" />
			</div>
		</div>
	);
}
