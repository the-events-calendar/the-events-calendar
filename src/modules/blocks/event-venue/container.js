/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import { globals } from '@moderntribe/common/utils';
import EventVenue from './template';
import { toVenue } from '@moderntribe/events/elements';
import { withStore, withForm } from '@moderntribe/common/hoc';
import { withDetails } from '@moderntribe/events/hoc';
import { actions, selectors } from '@moderntribe/events/data/blocks/venue';
import { actions as detailsActions } from '@moderntribe/events/data/details';
import { actions as formActions } from '@moderntribe/common/data/forms';
import { editor } from '@moderntribe/common/data';
import classicEventDetailsBlock from '@moderntribe/events/blocks/classic-event-details';
import {uniq} from "lodash";

/**
 * Module Code
 */

const setVenue = ( { state, dispatch, ownProps, venueID, details } ) => {
	const venues = selectors.getVenuesInClassic( state );

	ownProps.setAttributes( { venue: venueID } );
	ownProps.setAttributes( { venues: uniq( [ ...venues, venueID ] ) } );

	dispatch( detailsActions.setDetails( venueID, details ) );
	dispatch( actions.addVenueInClassic( venueID ) );
	dispatch( actions.addVenueInBlock( ownProps.name, venueID ) );
};

const onFormComplete = ( state, dispatch, ownProps ) => ( body ) => {
	setVenue( { state, dispatch, ownProps, venueID: body.id, details: body } );
};

const onFormSubmit = ( dispatch, ownProps ) => ( fields ) => (
	ownProps.sendForm( toVenue( fields ), onFormComplete( dispatch, ownProps ) )
);

const onItemSelect = ( dispatch, ownProps ) => setVenue( dispatch, ownProps );

const onCreateNew = ( ownProps ) => ( title ) => ownProps.createDraft( {
	title: {
		rendered: title,
	},
} );

const editVenue = ( ownProps ) => () => {
	const { details, editEntry } = ownProps;
	editEntry( details );
};

const mapStateToProps = ( state, ownProps ) => ( {
	venue: ownProps.attributes.venue,
	venues: selectors.getVenuesInBlock( state ),
	showMapLink: selectors.getshowMapLink( state ),
	showMap: selectors.getshowMap( state ),
	embedMap: selectors.getMapEmbed(),
	state,
} );

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
	editVenue: editVenue( ownProps ),
	onFormSubmit: onFormSubmit( dispatch, ownProps ),
	dispatch,
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => {
	const {state, ...restStateProps} = stateProps;
	const {dispatch, ...restDispatchProps} = dispatchProps;

	return {
		...ownProps,
		...restStateProps,
		...restDispatchProps,
		onItemSelect: ( venueID, details ) => {
			setVenue( { state, dispatch, ownProps, venueID, details } );
		},
		onRemove: () => {
			const { clientId, venue, details, volatile } = ownProps;

			ownProps.setAttributes( { venue: 0 } );
			dispatch( actions.removeVenueInBlock( clientId, venue ) );

			const blocks = globals.wpDataSelectCoreEditor().getBlocks();
			const classicBlock = blocks
				.filter( block => block.name === `tribe/${ classicEventDetailsBlock.id }` );

			if ( ! classicBlock.length || volatile ) {
				ownProps.maybeRemoveEntry( details );

				const venues = selectors.getVenuesInClassic( state );
				const newVenues = venues.filter( id => id !== venue );

				ownProps.setAttributes( { venues: newVenues } );
				dispatch( actions.removeVenueInClassic( venue ) );
				dispatch( formActions.removeVolatile( venue ) );
			}
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
