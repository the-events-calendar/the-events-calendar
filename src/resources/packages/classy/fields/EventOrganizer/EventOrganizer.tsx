import * as React from 'react';
import { Button, CustomSelectControl } from '@wordpress/components';
import { CustomSelectOption } from '@wordpress/components/build-types/custom-select-control/types';
import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { _x } from '@wordpress/i18n';
import { Fragment, MouseEventHandler, useRef } from 'react';
import { IconAdd } from '@tec/common/classy/components';
import OrganizerCards from './OrganizerCards';
import OrganizerUpsertModal from './OrganizerUpsertModal';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import { sortOptionsForDisplay } from '@tec/common/classy/functions/sortOptionsForDisplay';
import { FetchedOrganizer } from '../../types/FetchedOrganizer';
import { METADATA_EVENT_ORGANIZER_ID } from '../../constants';
import { OrganizerData } from '../../types/OrganizerData';
import { fetchOrganizers, upsertOrganizer } from '../../api/organizers';

function buildOptionFromFetchedOrganizer( organizer: FetchedOrganizer ): CustomSelectOption {
	return {
		key: organizer.id.toString(),
		name: organizer.organizer,
		value: organizer.id.toString(),
		label: organizer.organizer,
	};
}

const placeholderOption: CustomSelectOption = {
	key: '0',
	name: _x( 'Select organizer', 'Organizer selection placeholder option', 'the-events-calendar' ),
	value: '0',
};

function getUpdatedOptions( organizers: FetchedOrganizer[], currentOrganizerIds: number[] ) {
	return organizers
		.filter( ( organizer ) => ! currentOrganizerIds.includes( organizer.id ) )
		.map( buildOptionFromFetchedOrganizer )
		.sort( sortOptionsForDisplay );
}

export default function EventOrganizer( props: FieldProps ) {
	// Initially set the options to an array that only contains the placeholder.
	const [ options, setOptions ] = useState( [ placeholderOption ] );

	const meta = useSelect( ( select ): number[] => {
		const store: {
			getEditedPostAttribute: ( attribute: string ) => any;
		} = select( 'core/editor' );

		return store.getEditedPostAttribute( 'meta' ) || null;
	}, [] );

	const organizerIds =
		meta?.[ METADATA_EVENT_ORGANIZER_ID ]?.map( ( id: string ): number => parseInt( id, 10 ) ) || [];

	const { editPost } = useDispatch( 'core/editor' );

	const [ currentOrganizerIds, setCurrentOrganizerIds ] = useState( organizerIds );

	// To start, the page to fetch is the first one.
	const [ pageToFetch, setPageToFetch ] = useState( 1 );

	// If the initial number of organizers is 0, we are adding a new organizer.
	const [ isAdding, setIsAdding ] = useState( false );

	// Whether the user is currently inserting or updating an organizer or not.
	const [ isUpserting, setIsUpserting ] = useState< number | false >( false );

	const getOrganizerData = useCallback( (): OrganizerData => {
		const id = isUpserting || null;
		const organizer = id ? fetched.current.find( ( organizer: FetchedOrganizer ) => organizer.id === id ) : null;

		// Editing an existing organizer.
		if ( organizer ) {
			return {
				id: id,
				name: organizer.organizer,
				phone: organizer.phone,
				website: organizer.website,
				email: organizer.email,
			};
		}

		// Creating a new organizer or, somehow, trying to edit a non-yet fetched organizer.
		return {
			id: id,
			name: '',
			phone: '',
			website: '',
			email: '',
		};
	}, [ pageToFetch, isUpserting ] );

	// Keep a set of fetched Organizer objects across renders.
	const fetched = useRef( [] as FetchedOrganizer[] );

	useEffect( () => {
		const fetchOrganizersData = async () => {
			try {
				const result = await fetchOrganizers( pageToFetch );

				// Check that the organizers array is not empty, else return.
				if ( result.organizers.length === 0 ) {
					return;
				}

				// Update the number of pages to fetch if the total is more than the number of fetched options.
				if ( result.total > fetched.current.length + result.organizers.length ) {
					setPageToFetch( pageToFetch + 1 );
				}

				// Update the fetched set of organizers by making sure a new version of organizers will override the already fetched version.
				const safeResultIds = new Set( result.organizers.map( ( org ) => org.id ) );
				fetched.current = [
					...fetched.current.filter( ( org ) => ! safeResultIds.has( org.id ) ),
					...result.organizers,
				];

				// Update the options to all the so-far fetched options minus the current organizers ids.
				// Why not just add to the options? They might have been modified by a user removal or selection in the meanwhile.
				// Since we're recalculating them anyway, just make sure they are up to date.
				setOptions( getUpdatedOptions( fetched.current, currentOrganizerIds ) );
			} catch ( e ) {
				console.error( `Organizer fetch request failed for page ${ pageToFetch }: ` + e.message );
				// Set fetched organizers to an empty array if an error occurs
				fetched.current = [];
				setOptions( getUpdatedOptions( fetched.current, currentOrganizerIds ) );
			}
		};

		fetchOrganizersData();
	}, [ pageToFetch ] );

	const ref = useRef( null );

	function onOrganizerEdit( id: number ): void {
		setIsUpserting( id );
	}

	const onOrganizerSelect = useCallback(
		( newValue: { selectedItem: CustomSelectOption } ) => {
			// Add the new organizer to the current organizer ids.
			const organizerIds = [ ...currentOrganizerIds, parseInt( newValue.selectedItem.key ) ];
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
			const organizerIds = currentOrganizerIds.filter( ( organizerId: number ) => organizerId !== id );
			setCurrentOrganizerIds( organizerIds );

			// Update the options from the fetched ones: this operation might fire while the options are still being fetched.
			setOptions( getUpdatedOptions( fetched.current, organizerIds ) );

			editPost( {
				meta: { [ METADATA_EVENT_ORGANIZER_ID ]: organizerIds },
			} );
		},
		[ currentOrganizerIds ]
	);

	const createNewOrganizer: MouseEventHandler = () => {
		// We're creating a new organizer, the ID is 0.
		setIsUpserting( 0 );
	};

	// Display the selected organizers in the order set by the user in their selection.
	const orderedOrganizers: FetchedOrganizer[] = currentOrganizerIds
		.map( ( id: number ) => fetched.current.find( ( organizer ) => organizer.id === id ) )
		.filter( ( organizer: FetchedOrganizer | undefined ) => organizer !== undefined );

	/**
	 * Upserts an organizer by either updating an existing one or creating a new one based on the provided data.
	 *
	 * @since TBD
	 *
	 * @param {OrganizerData} organizerData The data of the organizer to be updated or inserted.
	 *
	 * @return {Promise<void>} A promise that resolves when the REST API replies.
	 */
	const handleOrganizerUpsert = useCallback( async ( organizerData: OrganizerData ) => {
		try {
			// Call the extracted upsertOrganizer function
			const organizerId = await upsertOrganizer( organizerData );

			// Get the organizer data from the API response
			const data: FetchedOrganizer = {
				id: organizerId,
				organizer: organizerData.name,
				phone: organizerData.phone,
				email: organizerData.email,
				website: organizerData.website,
				url: '', // This field is required by the FetchedOrganizer type but not used in the component
			};

			setIsUpserting( false );

			const index: number = fetched.current.findIndex( ( organizer ) => organizer.id === data.id );

			if ( index === -1 ) {
				// A new organizer has been created: add it to the fetched set of organizers.
				fetched.current.push( data );

				// Add the organizer ID to the list of current organizer IDs: we assume the user created the Organizer to add it.
				const newCurrentOrganizerIds = [ ...currentOrganizerIds, data.id ];
				setCurrentOrganizerIds( newCurrentOrganizerIds );

				// Update the post meta to track the new organizer IDs.
				editPost( {
					meta: {
						[ METADATA_EVENT_ORGANIZER_ID ]: newCurrentOrganizerIds,
					},
				} );

				// Update the options to the new set of organizers; this will trigger a re-render.
				setOptions( getUpdatedOptions( fetched.current, newCurrentOrganizerIds ) );
			} else {
				// An organizer has been updated: update it in the set of fetched organizers.
				fetched.current[ index ] = data;
				// Update the options to the new set; this will trigger a re-render.
				setOptions( getUpdatedOptions( fetched.current, currentOrganizerIds ) );
			}
		} catch ( error ) {
			setIsUpserting( false );
			// Set the page to fetch to 0 to make sure all the Organizers will be re-fetched.
			setPageToFetch( 0 );
			console.error( 'Organizer upsert request failed: ' + error.message );
		}
	}, [] );

	return (
		<div className="classy-field classy-field--event-organizer">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs classy-field__inputs--boxed">
				{ currentOrganizerIds.length > 0 && (
					<div className="classy-field__inputs-section">
						<OrganizerCards
							organizers={ orderedOrganizers }
							onEdit={ onOrganizerEdit }
							onRemove={ onOrganizerRemove }
						/>
					</div>
				) }

				{ isAdding && currentOrganizerIds.length > 0 && <span className="classy_section-separator"></span> }

				<div className="classy-field__inputs-section classy-field__inputs-section--row classy-field__inputs-section--justify-left">
					{ ( isAdding || currentOrganizerIds.length === 0 ) && (
						<Fragment>
							<div className="classy-field__input classy-field__input-full-width">
								<CustomSelectControl
									__next40pxDefaultSize
									className="classy-field__control classy-field__control--select"
									hideLabelFromVision={ true }
									label={ _x(
										'Organizer selection',
										'Assistive technology label',
										'the-events-calendar'
									) }
									onChange={ onOrganizerSelect }
									options={ options }
									value={ placeholderOption }
								/>
							</div>

							<div className="classy-field__input" ref={ ref }>
								<div className="classy-field__control classy-field__control--organizer" ref={ ref }>
									<span className="classy-field__organizer-label">
										{ _x(
											'or',
											'prefix to the Organizer create popover link ',
											'the-events-calendar'
										) }
									</span>{ ' ' }
									<Button
										variant="link"
										className="classy-cta classy-field__organizer-value"
										onClick={ createNewOrganizer }
									>
										{ _x(
											'Create new organizer',
											'Call to action to create a new organizer',
											'the-events-calendar'
										) }
									</Button>
								</div>
							</div>
						</Fragment>
					) }

					{ ! isAdding && currentOrganizerIds.length > 0 && (
						<div className="classy-field__input">
							<Button
								variant="link"
								className="classy-field__control classy-field__control--cta"
								onClick={ () => setIsAdding( true ) }
							>
								<IconAdd />
								{ _x(
									'Add another organizer',
									'Call-to-action to add another organizer',
									'the-events-calendar'
								) }
							</Button>
						</div>
					) }
				</div>

				{ currentOrganizerIds.length > 0 && isAdding && (
					<div className="classy-field__inputs-section classy-field__inputs-section--row">
						<Button
							variant="link"
							className="classy-field__control classy-field__control--cancel"
							onClick={ () => setIsAdding( false ) }
						>
							{ _x( 'Cancel', 'Cancel the organizer selection', 'the-events-calendar' ) }
						</Button>
					</div>
				) }

				{ isUpserting !== false && (
					<OrganizerUpsertModal
						isUpdate={ isUpserting > 0 }
						onCancel={ () => setIsUpserting( false ) }
						onSave={ handleOrganizerUpsert }
						onClose={ () => setIsUpserting( false ) }
						values={ getOrganizerData() }
					/>
				) }
			</div>
		</div>
	);
}
