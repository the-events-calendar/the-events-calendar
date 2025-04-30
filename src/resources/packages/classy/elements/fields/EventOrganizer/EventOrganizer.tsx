import React, { useRef, Fragment } from 'react';
import { useState, useEffect, useCallback } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import { _x } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { useSelect, useDispatch } from '@wordpress/data';
import OrganizerCards from './OrganizerCards';
import { FetchedOrganizer } from '../../../types/FetchedOrganizer';
import { METADATA_EVENT_ORGANIZER_ID } from '../../../constants';
import { usePostEdits } from '../../../hooks';
import { UsePostEditsReturn } from '../../../types/UsePostEditsReturn';
import AddIcon from '../../../elements/components/Icons/Add';
import { SelectOption } from '../../../types/SelectOption';
import { sortOptionsForDisplay } from '../../../functions/sortOptionsForDisplay';

function buildOptionFromFetchedOrganizer(
	organizer: FetchedOrganizer
): SelectOption {
	return {
		value: organizer.id.toString(),
		label: organizer.organizer,
	};
}

const placeholderOption: SelectOption = {
	value: '0',
	label: _x(
		'Select organizer',
		'Organizer selection placecholder option',
		'the-events-calendar'
	),
};

function getUpdatedOptions(
	organizers: FetchedOrganizer[],
	currentOrganizerIds: number[]
) {
	const newOptions = organizers
		.filter(
			( organizer ) => ! currentOrganizerIds.includes( organizer.id )
		)
		.map( buildOptionFromFetchedOrganizer )
		.sort( sortOptionsForDisplay );
	newOptions.unshift( placeholderOption );
	return newOptions;
}

export function EventOrganizer( props: { title: string } ) {
	// Initially set the options to an array that only contains the placeholder.
	const [ options, setOptions ] = useState( [ placeholderOption ] );

	const organizerIds = useSelect( ( select ) => {
		// @ts-ignore The method comes from a Classy store selectors.
		return select( 'tec/classy' ).getEditedPostOrganizerIds();
	}, [] );
	// @todo change this to useDispatch('core/editor') once registry redirction is in place.
	const { editPost } = usePostEdits() as UsePostEditsReturn;

	const [ currentOrganizerIds, setCurrentOrganizerIds ] =
		useState( organizerIds );

	// To start, the page to fetch is the first one.
	const [ pageToFetch, setPageToFetch ] = useState( 1 );

	// If the initial number of organizers is 0, we are adding a new organizer.
	const [ isAdding, setIsAdding ] = useState(
		currentOrganizerIds.length === 0
	);

	// Keep a set of fetched Organizer objects across renders.
	const fetched = useRef( [] as FetchedOrganizer[] );

	useEffect( () => {
		apiFetch( {
			path: addQueryArgs( '/tribe/events/v1/organizers', {
				page: pageToFetch,
			} ),
		} )
			.then( ( results ) => {
				// Check that results is an object, else log a console error.
				if ( ! ( results && typeof results === 'object' ) ) {
					console.error(
						'Organizer fetch request did not return an object.'
					);
					return;
				}

				// Check that the object has an `organizers` property, else log a console error.
				if (
					! (
						results.hasOwnProperty( 'organizers' ) &&
						results.hasOwnProperty( 'total' )
					)
				) {
					console.error(
						'Organizer fetch request did not return an object with organizers and total properties.'
					);
					return;
				}

				// Check that the `organizers` property is an array, else log a console error.
				if (
					! Array.isArray(
						( results as { organizers: any } ).organizers
					)
				) {
					console.error(
						'Organizer fetch request did not return an array.'
					);
					return;
				}

				// Check that the organizers array is not empty, else return.
				if (
					( results as { organizers: any[] } ).organizers.length === 0
				) {
					return;
				}

				const safeResults = results as {
					organizers: FetchedOrganizer[];
					total: number;
				};

				// Update the number of pages to fetch if the total is more than the number of fetched options.
				if (
					safeResults.total >
					options.length + safeResults.organizers.length
				) {
					setPageToFetch( pageToFetch + 1 );
				}

				const newOrganizers = safeResults.organizers.map(
					buildOptionFromFetchedOrganizer
				);

				// Udpate the fetched set of organizers.
				fetched.current = [
					...fetched.current,
					...safeResults.organizers,
				];

				// Update the options to all the so-far fetched options minus the current organizers ids.
				// Why not just add to the options? They might have been modified by a user removal or selection in the meanwhile.
				// Since we're recalculating them anyway, just make sure they are up to date.
				setOptions(
					getUpdatedOptions( fetched.current, currentOrganizerIds )
				);
			} )
			.catch( ( e ) => {
				console.error( 'Organizer fetch request failed: ' + e.message );
			} );
	}, [ pageToFetch ] );

	const ref = useRef( null );

	function onOrganizerEdit( id: number ): void {
		console.log( 'Organizer edit not implemented yet.' );
	}

	const onOrganizerSelect = useCallback(
		( newValue: string ) => {
			// Add the new organizer to the current organizer ids.
			const organizerIds = [
				...currentOrganizerIds,
				parseInt( newValue ),
			];
			setCurrentOrganizerIds( organizerIds );

			// Update the options from the fetched ones: this operation might fire while the options are still being fetched.
			setOptions( getUpdatedOptions( fetched.current, organizerIds ) );

			// Close the add operation. This will serve the most common case of a user adding one organizer.
			setIsAdding( false );

			editPost( {
				meta: { [ METADATA_EVENT_ORGANIZER_ID ]: organizerIds },
			} );
		},
		[ currentOrganizerIds ]
	);

	const onOrganizerRemove = useCallback(
		( id: number ): void => {
			// Remove the organizer from the current organizer ids.
			const organizerIds = currentOrganizerIds.filter(
				( organizerId ) => organizerId !== id
			);
			setCurrentOrganizerIds( organizerIds );

			// Update the options from the fetched ones: this operation might fire while the options are still being fetched.
			setOptions( getUpdatedOptions( fetched.current, organizerIds ) );

			editPost( {
				meta: { [ METADATA_EVENT_ORGANIZER_ID ]: organizerIds },
			} );
		},
		[ currentOrganizerIds ]
	);

	// Display the selected organizers in the order set by the user in their selection.
	const orderedOrganizers = currentOrganizerIds
		.map( ( id: number ) =>
			fetched.current.find( ( organizer ) => organizer.id === id )
		)
		.filter( ( organizer ) => organizer !== undefined );

	return (
		<div className="classy-field classy-field--event-organizer">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs">
				<div className="classy-field__inputs-section">
					{ currentOrganizerIds.length > 0 && (
						<OrganizerCards
							organizers={ orderedOrganizers }
							onEdit={ onOrganizerEdit }
							onRemove={ onOrganizerRemove }
						/>
					) }
				</div>

				{ isAdding && (
					<span className="classy_section-separator"></span>
				) }

				<div className="classy-field__inputs-section classy-field__inputs-section--row">
					{ isAdding && (
						<Fragment>
							<div className="classy-field__input classy-field__input-full-width">
								<SelectControl
									className="classy-field__control classy-field__control--select"
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									value={ 0 }
									options={ options }
									onChange={ onOrganizerSelect }
								/>
							</div>

							<div className="classy-field__input" ref={ ref }>
								<div
									className="classy-field__control classy-field__control--organizer"
									ref={ ref }
								>
									<span className="classy-field__organizer-label">
										{ _x(
											'or',
											'prefix to the Organizer create popover link ',
											'the-events-calendar'
										) }
									</span>{ ' ' }
									<a
										className="classy-field__organizer-value"
										href="#"
										onClick={ () => {
											console.log(
												'Organizer creation not implemented yet.'
											);
										} }
									>
										{ _x(
											'Create new organizer',
											'Call to action to create a new organizer',
											'the-events-calendar'
										) }
									</a>
								</div>
							</div>
						</Fragment>
					) }

					{ ! isAdding && (
						<div className="classy-field__input">
							<a
								href="#"
								className="classy-field__control classy-field__control--cta"
								onClick={ () => setIsAdding( true ) }
							>
								<AddIcon />
								{ _x(
									'Add another organizer',
									'Call-to-action to add another organizer',
									'the-events-calendar'
								) }
							</a>
						</div>
					) }
				</div>

				{ currentOrganizerIds.length > 0 && isAdding && (
					<div className="classy-field__inputs-section classy-field__inputs-section--row">
						<a
							href="#"
							className="classy-field__control classy-field__control--cancel"
							onClick={ () => setIsAdding( false ) }
						>
							{ _x(
								'Cancel',
								'Cancel the organizer selection',
								'the-events-calendar'
							) }
						</a>
					</div>
				) }
			</div>
		</div>
	);
}
