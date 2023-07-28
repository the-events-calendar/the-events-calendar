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
import { withDetails } from '@moderntribe/events/hoc';
import { actions, selectors } from '@moderntribe/events/data/blocks/venue';
import { actions as detailsActions } from '@moderntribe/events/data/details';
import { editor } from '@moderntribe/common/data';
import { getVenuesInBlock } from '../../data/blocks/venue/selectors';

/**
 * Module Code
 */

const setVenue = ( { state, dispatch, ownProps, venueID, details } ) => {
	const venues = selectors.getVenuesInBlock( state );

	ownProps.setAttributes( { venue: venueID } );
	ownProps.setAttributes( { venues: uniq( [ ...venues, venueID ] ) } );

	dispatch( detailsActions.setDetails( venueID, details ) );
	dispatch( actions.addVenueInBlock( ownProps.clientId, venueID ) );
};

const onFormComplete = ( state, dispatch, ownProps ) => ( body ) => {
	setVenue( { state, dispatch, ownProps, venueID: body.id, details: body } );
};

const onFormSubmit = ( dispatch, ownProps ) => ( fields ) => (
	ownProps.sendForm( toVenue( fields ), onFormComplete( dispatch, ownProps ) )
);

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
	showMapLink: ownProps.attributes.showMapLink || true,
	showMap: ownProps.attributes.showMap || true,
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
