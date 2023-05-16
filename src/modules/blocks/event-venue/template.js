/**
 * External dependencies
 */
import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { isEmpty } from 'lodash';
import { addressToMapString } from '@moderntribe/events/editor/utils/geo-data';
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
import { editor } from '@moderntribe/common/data';
import VenueDetails from './venue-details';
import { Venue as VenueIcon } from '@moderntribe/events/icons';
import { utils } from '@moderntribe/events/data/blocks/venue';
import { google, wpEditor } from '@moderntribe/common/utils/globals';
import './style.pcss';

const { InspectorControls } = wpEditor;
const { getAddress } = utils;
const { maps } = google();
const geocoder = new maps.Geocoder();

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
		removeVenue: PropTypes.func,
		editVenue: PropTypes.func,
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
		const { details } = this.props;
		const address = addressToMapString( getAddress( details ) );
		if ( address ) {
			this.setCoordinatesState( address );
		}
	}

	componentDidUpdate( prevProps ) {
		const { isSelected, edit, create, setSubmit, details } = this.props;
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
		const { fields, onFormSubmit } = this.props;
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
			editVenue,
		} = this.props;

		const idle = edit || create || isLoading || submit;
		if ( ! this.hasVenue() || ! isSelected || ! volatile || idle ) {
			return null;
		}

		return (
			<button onClick={ editVenue }>
				<Dashicon icon="edit" />
			</button>
		);
	}

	renderDetails = () => {
		const { showMapLink, details } = this.props;

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
	}

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

	renderContainer() {
		const { isLoading, edit, create, submit } = this.props;
		if ( isLoading || submit ) {
			return (
				<Placeholder key="loading">
					<Spinner />
				</Placeholder>
			);
		}

		if ( edit || create ) {
			return this.renderForm();
		}

		return this.hasVenue() ? this.renderDetails() : this.renderSearchOrCreate();
	}

	renderMap() {
		const { details, edit, create, isLoading, submit, showMap } = this.props;
		if ( ! showMap || isEmpty( details ) || edit || create || isLoading || submit ) {
			return null;
		}
		const { coords } = this.state;
		return (
			<GoogleMap
				size={ { width: 450, height: 353 } }
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
			removeVenue,
		} = this.props;

		if ( ! this.hasVenue() || ! isSelected || edit || create || isLoading || submit ) {
			return null;
		}

		return (
			<div className="tribe-editor__venue__actions">
				<button
					className="tribe-editor__venue__actions--close"
					onClick={ removeVenue }
				>
					{ __( 'Remove venue', 'the-events-calendar' ) }
				</button>
			</div>
		);
	}

	renderBlock() {
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
					/>
					{ embedMap && (
						<ToggleControl
							label={ __( 'Show Google Maps Embed', 'the-events-calendar' ) }
							checked={ showMap }
							onChange={ toggleVenueMap }
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
	 * Given how withDetails is currently tightly coupled with the state, this cannot
	 * be moved to the container. withDetails should be decoupled from state.
	 *
	 * @todo  this hasVenue is coupled to the existence of details, not the venue ID.
	 * @return {boolean}
	 */
	hasVenue() {
		const { details } = this.props;
		return ! isEmpty( details );
	}

	/**
	 * Once withDetails is decoupled from state, this should move to container.
	 *
	 * @todo this function cannot be moved to container as it depends on hasVenue().
	 * @return {void}
	 */
	maybeEdit = () => {
		const { volatile, editVenue } = this.props;
		if ( this.hasVenue() && volatile ) {
			return editVenue;
		}
	}

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
	}
}

export default EventVenue;
