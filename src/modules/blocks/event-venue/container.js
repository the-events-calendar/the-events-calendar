/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose } from 'redux';
import { uniq } from 'lodash';

/**
 * Internal dependencies
 */
import EventVenue from './template';
import { toVenue } from '@moderntribe/events/elements';
import { withStore, withForm } from '@moderntribe/common/hoc';
import { store } from '@moderntribe/common/store';
import { withDetails } from '@moderntribe/events/hoc';
import { actions, selectors } from '@moderntribe/events/data/blocks/venue';
import { actions as detailsActions } from '@moderntribe/events/data/details';
import { editor } from '@moderntribe/common/data';
import { syncVenuesWithPost } from "./data/meta-sync";
import { globals } from '@moderntribe/common/utils';
const { getState } = store;

/**
 * Module Code
 */

/**
 * Sets the venue in the block.
 *
 * Sets the attributes, updates state, updates details, and syncs with post meta.
 *
 * @param {Object} params
 * @param {Object} params.state
 * @param {Function} params.dispatch
 * @param {Object} params.ownProps
 * @param {number} params.venueID
 * @param {Object} params.details
 */
const setVenue = ( { state, dispatch, ownProps, venueID, details } ) => {
	const venues = selectors.getVenuesInBlock( state );

	ownProps.setAttributes( { venue: venueID } );
	ownProps.setAttributes( { venues: uniq( [ ...venues, venueID ] ) } );

	dispatch( detailsActions.setDetails( venueID, details ) );
	dispatch( actions.addVenueInBlock( ownProps.clientId, venueID ) );

	// Set post meta to have the current collection of venues.
	syncVenuesWithPost();
};

/**
 * One the venue form has been filled out, set the venue in the block.
 *
 * @param {Object} state
 * @param {Function} dispatch
 * @param {Object} ownProps
 * @returns {(function(*): void)|*}
 */
const onFormComplete = ( state, dispatch, ownProps ) => ( body ) => {
	setVenue( { state, dispatch, ownProps, venueID: body.id, details: body } );
};

/**
 * Handles form submission.
 *
 * @param {Function} dispatch
 * @param {Object} ownProps
 * @returns {(function(*): void)|*}
 */
const onFormSubmit = ( dispatch, ownProps ) => ( fields ) => {
	ownProps.sendForm( toVenue( fields ), onFormComplete( getState(), dispatch, ownProps ) );
};

/**
 * Creates a draft venue.
 *
 * @param {Object} ownProps
 * @returns {Function}
 */
const onCreateNew = ( ownProps ) => ( title ) => ownProps.createDraft( {
	title: {
		rendered: title,
	},
} );

/**
 * Triggers the editEntry operation.
 *
 * @param {Object} ownProps
 */
const onEdit = ( ownProps ) => () => {
	const { details, editEntry } = ownProps;
	editEntry( details );
};

const mapStateToProps = ( state, ownProps ) => {
	let showMapLink = true;
	let showMap = true;

	if ( ownProps.attributes.showMapLink !== undefined ) {
		showMapLink = ownProps.attributes.showMapLink;
	}

	if ( ownProps.attributes.showMap !== undefined ) {
		showMap = ownProps.attributes.showMap;
	}

	return ( {
		venue: ownProps.attributes.venue,
		venues: selectors.getVenuesInBlock( state ),
		showMapLink: showMapLink,
		showMap: showMap,
		embedMap: selectors.getMapEmbed(),
		state,
	} );
};

const mapDispatchToProps = ( dispatch, ownProps ) => ( {
	toggleVenueMap: ( value ) => {
		ownProps.setAttributes( { showMap: value } );
		dispatch( actions.setShowMap( value ) );
	},
	toggleVenueMapLink: ( value ) => {
		ownProps.setAttributes( { showMapLink: value } );
		dispatch( actions.setShowMapLink( value ) );
	},
	onCreateNew: onCreateNew( ownProps ),
	onEdit: onEdit( ownProps ),
	onFormSubmit: onFormSubmit( dispatch, ownProps ),
	dispatch,
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => {
	const { state, ...restStateProps } = stateProps;
	const { dispatch, ...restDispatchProps } = dispatchProps;

	return {
		...ownProps,
		...restStateProps,
		...restDispatchProps,
		onItemSelect: ( venueID, details ) => {
			setVenue( { state, dispatch, ownProps, venueID, details } );
		},
		onRemove: () => {
			const { clientId, venue } = ownProps;

			ownProps.setAttributes( { venue: 0 } );
			dispatch( actions.removeVenueInBlock( clientId, venue ) );

			/**
			 * Moves the venue to the trash if appropriate (if it is a draft and was removed).
			 *
			 * @since 6.2.0
			 * @param {number} venue
			 */
			globals.wpHooks.doAction( 'tec.events.blocks.venue.maybeRemoveVenue', venue );

			syncVenuesWithPost();
		},
	};
};

export default compose(
	withStore( { postType: editor.VENUE } ),
	connect( mapStateToProps ),
	withDetails( 'venue' ),
	withForm( ( props ) => props.name ),
	connect( mapStateToProps, mapDispatchToProps, mergeProps ),
)( EventVenue );
