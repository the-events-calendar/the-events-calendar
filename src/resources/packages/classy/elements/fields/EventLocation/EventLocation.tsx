import apiFetch from '@wordpress/api-fetch';
import { Button, CustomSelectControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { _x } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import React, {
	useCallback,
	useEffect,
	useRef,
	useState,
	Fragment,
} from 'react';
import { METADATA_EVENT_VENUE_ID } from '../../../constants';
import AddIcon from '../../../elements/components/Icons/Add';
import VideoCameraIcon from '../../components/Icons/VideoCamera';
import { FetchedVenue } from '../../../types/FetchedVenue';
import VenueCards from './VenueCards';
import { CustomSelectOption } from '@wordpress/components/build-types/custom-select-control/types';
import { sortOptionsForDisplay } from '../../../functions/sortOptionsForDisplay';

function buildOptionFromFetchedVenue(
	venue: FetchedVenue
): CustomSelectOption {
	return {
		key: venue.id.toString(),
		name: venue.venue,
		value: venue.id.toString(),
		label: venue.venue,
	};
}

const placeholderOption: CustomSelectOption = {
	key: '0',
	name: _x(
		'Select venue',
		'Venue selection placecholder option',
		'the-events-calendar'
	),
	value: '0',
};

function getUpdatedOptions(
	venues: FetchedVenue[],
	currentVenueIds: number[]
) {
	return venues
		.filter( ( venue ) => ! currentVenueIds.includes( venue.id ) )
		.map( buildOptionFromFetchedVenue )
		.sort( sortOptionsForDisplay );
}

export function EventLocation( props: { title: string } ) {
	// Initially set the options to an array that only contains the placeholder.
	const [ options, setOptions ] = useState( [ placeholderOption ] );

	const venueIds = useSelect( ( select ): number[] => {
		const selector = select( 'core/editor' );
		// @ts-ignore
		return ( selector.getEditedPostAttribute( 'meta' ) || [] )?.[
			METADATA_EVENT_VENUE_ID
		].map( ( id: string ): number => parseInt( id, 10 ) );
	}, [] );

	const { editPost } = useDispatch( 'core/editor' );

	const [ currentVenueIds, setCurrentVenueIds ] = useState( venueIds );

	// To start, the page to fetch is the first one.
	const [ pageToFetch, setPageToFetch ] = useState( 1 );

	// If the initial number of venues is 0, we are adding a new venue.
	const [ isAdding, setIsAdding ] = useState( false );

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
					console.error(
						'Venues fetch request did not return an object.'
					);
					return;
				}

				// Check that the object has an `venues` property, else log a console error.
				if (
					! (
						results.hasOwnProperty( 'venues' ) &&
						results.hasOwnProperty( 'total' )
					)
				) {
					console.error(
						'Venues fetch request did not return an object with venues and total properties.'
					);
					return;
				}

				// Check that the `venues` property is an array, else log a console error.
				if (
					! Array.isArray( ( results as { venues: any } ).venues )
				) {
					console.error(
						'Venues fetch request did not return an array.'
					);
					return;
				}

				// Check that the venues array is not empty, else return.
				if ( ( results as { venues: any[] } ).venues.length === 0 ) {
					return;
				}

				const safeResults = results as {
					venues: FetchedVenue[];
					total: number;
				};

				// Update the number of pages to fetch if the total is more than the number of fetched options.
				if (
					safeResults.total >
					fetched.current.length + safeResults.venues.length
				) {
					setPageToFetch( pageToFetch + 1 );
				}

				const newVenues = safeResults.venues.map(
					buildOptionFromFetchedVenue
				);

				// Update the fetched set of venues by making sure new version of venues will override the already fetched version.
				const safeResultIds = new Set(
					safeResults.venues.map( ( org ) => org.id )
				);
				fetched.current = [
					...fetched.current.filter(
						( org ) => ! safeResultIds.has( org.id )
					),
					...safeResults.venues,
				];

				// Update the options to all the so-far fetched options minus the current venue ids.
				// Why not just add to the options? They might have been modified by a user removal or selection in the meanwhile.
				// Since we're recalculating them anyway, just make sure they are up to date.
				setOptions(
					getUpdatedOptions( fetched.current, currentVenueIds )
				);
			} )
			.catch( ( e ) => {
				console.error( 'Venue fetch request failed: ' + e.message );
			} );
	}, [ pageToFetch ] );

	const ref = useRef( null );

	function onVenueEdit( id: number ): void {
		console.log( 'Venue edit not implemented yet.' );
	}

	const onVenueSelect = useCallback(
		( newValue: { selectedItem: CustomSelectOption } ) => {
			const venueIds = [
				...currentVenueIds,
				parseInt( newValue.selectedItem.key ),
			];
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
			const venueIds = currentVenueIds.filter(
				( venueId ) => venueId !== id
			);
			setCurrentVenueIds( venueIds );

			setOptions( getUpdatedOptions( fetched.current, venueIds ) );

			editPost( {
				meta: { [ METADATA_EVENT_VENUE_ID ]: venueIds },
			} );
		},
		[ currentVenueIds ]
	);

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
						<VenueCards
							venues={ orderedVenues }
							onEdit={ onVenueEdit }
							onRemove={ onVenueRemove }
						/>
					</div>
				) }

				{ isAdding && (
					<span className="classy_section-separator"></span>
				) }

				<div className="classy-field__inputs-section classy-field__inputs-section--row">
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
									value={ 0 }
								/>
							</div>

							<div className="classy-field__input" ref={ ref }>
								<div
									className="classy-field__control classy-field__control--venue"
									ref={ ref }
								>
									<span className="classy-field__venue-label">
										{ _x(
											'or',
											'prefix to the Venue create popover link ',
											'the-events-calendar'
										) }
									</span>{ ' ' }
									<Button
										variant="link"
										className="classy-field__venue-value"
										onClick={ () => {
											console.log(
												'Venue creation not implemented yet.'
											);
										} }
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

					{ ! isAdding && currentVenueIds.length > 0 && (
						<div className="classy-field__input">
							<Button
								variant="link"
								className="classy-field__control classy-field__control--cta"
								onClick={ () => setIsAdding( true ) }
							>
								<AddIcon />
								{ _x(
									'Add another venue',
									'Call-to-action to add another venue',
									'the-events-calendar'
								) }
							</Button>
						</div>
					) }
				</div>

				{ currentVenueIds.length > 0 && isAdding && (
					<div className="classy-field__inputs-section classy-field__inputs-section--row">
						<Button
							variant="link"
							className="classy-field__control classy-field__control--cancel"
							onClick={ () => setIsAdding( false ) }
						>
							{ _x(
								'Cancel',
								'Cancel the venue selection',
								'the-events-calendar'
							) }
						</Button>
					</div>
				) }

				<div className="classy-field__inputs-section classy-field__inputs-section--row classy-field__inputs-section--footer">
					<Button
						variant="link"
						className="classy-field__control classy-field__control--cta"
						onClick={ () =>
							console.log(
								'Virtual event details not implemented yet'
							)
						}
					>
						<VideoCameraIcon />
						{ _x(
							'Add virtual event details',
							'Cancel the venue selection',
							'the-events-calendar'
						) }
					</Button>
				</div>
			</div>
		</div>
	);
}
