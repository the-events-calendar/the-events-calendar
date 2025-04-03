/**
 * External dependencies
 */
import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { isEmpty, isInteger, get } from 'lodash';
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	Spinner,
	Placeholder,
	ToggleControl,
	PanelBody,
	Dashicon,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import {
	SearchOrCreate,
	VenueForm,
	toFields,
	GoogleMap,
	EditLink,
} from '@moderntribe/events/elements';
import { addressToMapString } from '@moderntribe/events/editor/utils/geo-data';
import { editor } from '@moderntribe/common/data';
import VenueDetails from './venue-details';
import { Venue as VenueIcon } from '@moderntribe/events/icons';
import { utils } from '@moderntribe/events/data/blocks/venue';
import { google, wpEditor, wpHooks } from '@moderntribe/common/utils/globals';
import './style.pcss';

const { InspectorControls } = wpEditor;
const { getAddress } = utils;

/**
 * Module Code
 */

class EventVenue extends Component {
	static propTypes = {
		venue: PropTypes.number,
		isSelected: PropTypes.bool,
		isLoading: PropTypes.bool,
		submit: PropTypes.bool,
		edit: PropTypes.bool,
		create: PropTypes.bool,
		details: PropTypes.object,
		draft: PropTypes.object,
		showMap: PropTypes.bool,
		showMapLink: PropTypes.bool,
		embedMap: PropTypes.bool,
		createDraft: PropTypes.func,
		editDraft: PropTypes.func,
		removeDraft: PropTypes.func,
		setDraftDetails: PropTypes.func,
		clear: PropTypes.func,
		sendForm: PropTypes.func,
		toggleVenueMap: PropTypes.func,
		toggleVenueMapLink: PropTypes.func,
		onFormSubmit: PropTypes.func,
		onItemSelect: PropTypes.func,
		onCreateNew: PropTypes.func,
		onRemove: PropTypes.func,
		onEdit: PropTypes.func,
		volatile: PropTypes.any,
		name: PropTypes.any,
		store: PropTypes.any,
		fields: PropTypes.any,
		setSubmit: PropTypes.any,
	};

	constructor( props ) {
		super( props );

		/**
		 * @todo move this into the Store
		 */
		this.state = { coords: { lat: null, lng: null }, derivedAddressString: '' };
	}

	componentDidMount() {
		let { details } = this.props;

		if ( this.hasVenue() ) {
			details = this.getVenueDetails();
		}

		const address = addressToMapString( getAddress( details ) );
		if ( address ) {
			this.setCoordinatesState( address );
		}
	}

	componentDidUpdate( prevProps ) {
		const { isSelected, edit, create, setSubmit } = this.props;
		let { details } = this.props;

		if ( this.hasVenue() ) {
			details = this.getVenueDetails();
		}

		const unSelected = prevProps.isSelected && ! isSelected;
		const address = addressToMapString( getAddress( details ) );
		const { derivedAddressString } = this.state;

		if ( unSelected && ( edit || create ) ) {
			setSubmit();
		}
		// Did we change?
		if ( derivedAddressString !== address ) {
			// Get coords if we did.
			this.setCoordinatesState( address );
		}
	}

	renderForm() {
		const {
			isSelected,
			fields,
			onFormSubmit,
		} = this.props;

		if ( ! isSelected ) {
			return null;
		}

		return (
			<VenueForm
				{ ...toFields( fields ) }
				onSubmit={ onFormSubmit }
			/>
		);
	}

	renderEditAction() {
		const {
			isSelected,
			edit,
			create,
			isLoading,
			submit,
			volatile,
			onEdit,
		} = this.props;

		const idle = edit || create || isLoading || submit;
		if ( ! isSelected || ! volatile || idle ) {
			return null;
		}

		return (
			<button onClick={ onEdit }>
				<Dashicon icon="edit" />
			</button>
		);
	}

	renderDetails = () => {
		const { showMapLink } = this.props;

		const details = this.getVenueDetails();

		return (
			<VenueDetails
				venue={ details }
				address={ getAddress( details ) }
				showMapLink={ showMapLink }
				afterTitle={ this.renderEditAction() }
				maybeEdit={ this.maybeEdit }
				removeVenue={ this.renderRemoveAction() }
			/>
		);
	};

	renderSearchOrCreate() {
		// @todo [BTRIA-618]: The store should not be passed through like this as a prop.
		// Instead, we should hook up the element with a HOC.
		const { isSelected, store, name, onItemSelect, onCreateNew } = this.props;
		return (
			<SearchOrCreate
				name={ name }
				icon={ <VenueIcon /> }
				store={ store }
				isSelected={ isSelected }
				postType={ editor.VENUE }
				onItemSelect={ onItemSelect }
				onCreateNew={ onCreateNew }
				placeholder={ __( 'Add or find a venue', 'the-events-calendar' ) }
			/>
		);
	}

	renderLoading = () => (
		<Placeholder key="loading">
			<Spinner />
		</Placeholder>
	);

	renderContainer() {
		const { isSelected, isLoading, edit, create, submit } = this.props;

		if ( isLoading || submit ) {
			return this.renderLoading();
		}

		if ( isSelected && ( edit || create ) ) {
			return this.renderForm();
		}

		if ( ! this.hasVenue() ) {
			return this.renderSearchOrCreate();
		}

		return this.renderDetails();
	}

	renderMap() {
		const { edit, create, isLoading, submit, showMap } = this.props;
		const details = this.getVenueDetails();

		if ( ! showMap || isEmpty( details ) || edit || create || isLoading || submit ) {
			return null;
		}

		const { coords } = this.state;
		return (
			<GoogleMap
				size={ { width: 450, height: 220 } }
				coordinates={ coords }
				address={ addressToMapString( getAddress( details ) ) }
				interactive={ true }
			/>
		);
	}

	renderRemoveAction() {
		const {
			isSelected,
			edit,
			create,
			isLoading,
			submit,
			onRemove,
		} = this.props;

		if ( ! this.hasVenue() || ! isSelected || edit || create || isLoading || submit ) {
			return null;
		}

		if ( ! this.isAuthoritativeVenue() ) {
			return null;
		}

		return (
			<div className="tribe-editor__venue__actions">
				<button
					className="tribe-editor__venue__actions--close"
					onClick={ onRemove }
				>
					{ __( 'Remove venue', 'the-events-calendar' ) }
				</button>
			</div>
		);
	}

	renderBlock() {
		const { isLoading } = this.props;

		if ( isLoading ) {
			return this.renderLoading();
		}

		const containerClass = classNames( {
			'tribe-editor__venue': this.hasVenue(),
			'tribe-editor__venue--has-map': this.hasVenue() && this.props.showMap,
		} );

		return (
			<div key="event-venue-box" className={ containerClass }>
				{ this.renderContainer() }
				{ this.renderMap() }
			</div>
		);
	}

	renderControls() {
		const {
			venue,
			showMapLink,
			showMap,
			embedMap,
			toggleVenueMap,
			toggleVenueMapLink,
		} = this.props;

		if ( ! this.hasVenue() ) {
			return null;
		}

		return (
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'Venue Settings', 'the-events-calendar' ) }>
					<ToggleControl
						label={ __( 'Show Google Maps Link', 'the-events-calendar' ) }
						checked={ showMapLink }
						onChange={ toggleVenueMapLink }
						__nextHasNoMarginBottom={ true }
					/>
					{ embedMap && (
						<ToggleControl
							label={ __( 'Show Google Maps Embed', 'the-events-calendar' ) }
							checked={ showMap }
							onChange={ toggleVenueMap }
							__nextHasNoMarginBottom={ true }
						/>
					) }
					<EditLink
						postId={ venue }
						label={ __( 'Edit Venue', 'the-events-calendar' ) }
					/>
				</PanelBody>
			</InspectorControls>
		);
	}

	render() {
		return [ this.renderBlock(), this.renderControls() ];
	}

	/**
	 * Gets the venue details for the block.
	 *
	 * @since 6.2.0
	 * @returns {Object} Venue details.
	 */
	getVenueDetails() {
		const venueId = this.getVenueId();

		if ( ! isInteger( venueId ) ) {
			return {};
		}

		const state = this.props.store.getState();

		return get( state, `events.details[${ venueId }].details`, {} );
	}

	/**
	 * Gets the venue ID for the block.
	 *
	 * @since 6.2.0
	 * @since 6.3.1 This will now return the value of the `venue` prop.
	 * @returns {number|null} Venue ID or null.
	 */
	getVenueId() {
		const state = this.props.store.getState();
		let venueId = this.props.venue;

		/**
		 * Filters the venue ID to be used for the block.
		 *
		 * @since 6.2.0
		 * @param {number} venueId The venue ID.
		 * @param {Object} props The block props.
		 * @param {Object} state The tribe common state.
		 * @return {number} The venue ID.
		 */
		venueId = wpHooks.applyFilters(
			'tec.events.blocks.tribe_event_venue.getVenueId',
			venueId,
			this.props,
			state,
		);

		if ( ! isInteger( venueId ) ) {
			return null;
		}

		return venueId;
	}

	/**
	 * Given how withDetails is currently tightly coupled with the state, this cannot
	 * be moved to the container. withDetails should be decoupled from state.
	 *
	 * @return {boolean}
	 */
	hasVenue() {
		const details = this.getVenueDetails();

		return ! isEmpty( details ) && isInteger( this.getVenueId() );
	}

	/**
	 * Whether or not the venue block is an authoritative one.
	 *
	 * Authoritative means it is a block that is showing the venue that was explicitly selected for it rather
	 * than a cloned representation of a venue from another block.
	 *
	 * @since 6.2.0
	 * @returns {boolean} Whether the venue is authoritative.
	 */
	isAuthoritativeVenue = () => {
		const { venue } = this.props;

		return isInteger( venue ) && venue === this.getVenueId();
	};

	/**
	 * Once withDetails is decoupled from state, this should move to container.
	 *
	 * @todo this function cannot be moved to container as it depends on hasVenue().
	 * @return {void}
	 */
	maybeEdit = () => {
		const { volatile, onEdit } = this.props;
		if ( this.hasVenue() && volatile ) {
			return onEdit;
		}
	};

	/**
	 * Get the coordinates according to the venue address
	 * So we can display the map on the backend
	 *
	 * @todo  We need to save the data into Meta Fields to avoid redoing the Geocode
	 * @todo  Move the Maps into Pro
	 * @param  {string} address Address string for geocode query.
	 * @return {void}
	 */
	setCoordinatesState = ( address ) => {
		const { maps } = google();
		const geocoder = new maps.Geocoder();

		// Clear our state?
		if ( ! address ) {
			this.setState( { coords: { lat: null, lng: null }, derivedAddressString: '' } );
			return;
		}

		// Fetch coords.
		geocoder.geocode( { address: address }, ( results, status ) => {
			if ( 'OK' !== status ) {
				this.setState( ( state ) => ( { ...state, derivedAddressString: address } ) );
				return;
			}

			const { location } = results[ 0 ].geometry;
			this.setState( {
				coords: { lat: location.lat(), lng: location.lng() },
				derivedAddressString: address,
			} );
		} );
	};
}

export default EventVenue;
