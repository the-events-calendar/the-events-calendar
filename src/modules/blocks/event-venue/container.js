/**
 * External dependencies
 */
import { connect } from 'react-redux';
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import EventVenue from './template';
import { toVenue } from '@moderntribe/events/elements';
import { withStore, withForm } from '@moderntribe/common/hoc';
import { withDetails } from '@moderntribe/events/hoc';
import { actions, selectors } from '@moderntribe/events/data/blocks/venue';
import { editor } from '@moderntribe/common/data';

/**
 * Module Code
 */

const setVenue = ( stateProps, dispatch, ownProps ) => ( id ) => {
	ownProps.setAttributes( { venue: id } );
	ownProps.setAttributes( { showMapLink: stateProps.showMapLink } );
	ownProps.setAttributes( { showMap: stateProps.showMap } );
	dispatch( actions.setVenue( id ) );
};

const onFormComplete = ( stateProps, dispatch, ownProps ) => ( body ) => {
	const { setDetails } = ownProps;
	const { id } = body;
	setDetails( id, body );
	setVenue( stateProps, dispatch, ownProps )( id );
};

const onFormSubmit = ( stateProps, dispatch, ownProps ) => ( fields ) => (
	ownProps.sendForm( toVenue( fields ), onFormComplete( stateProps, dispatch, ownProps ) )
);

const onItemSelect = ( stateProps, dispatch, ownProps ) => setVenue( stateProps, dispatch, ownProps );

const onCreateNew = ( ownProps ) => ( title ) => ownProps.createDraft( {
	title: {
		rendered: title,
	},
} );

// TODO: need to remove the use of "maybe" functions as they hold logic they
// ultimately should not.
const removeVenue = ( dispatch, ownProps ) => () => {
	const { volatile, maybeRemoveEntry, details } = ownProps;

	ownProps.setAttributes( { venue: 0 } );
	dispatch( actions.removeVenue() );
	if ( volatile ) {
		maybeRemoveEntry( details );
	}
};

const editVenue = ( ownProps ) => () => {
	const { details, editEntry } = ownProps;
	editEntry( details );
};

const mapStateToProps = ( state ) => ( {
	venue: selectors.getVenue( state ),
	showMapLink: selectors.getshowMapLink( state ),
	showMap: selectors.getshowMap( state ),
	embedMap: selectors.getMapEmbed(),
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
	removeVenue: removeVenue( dispatch, ownProps ),
	editVenue: editVenue( ownProps ),
	dispatch,
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => {
	const { dispatch, ...restDispatchProps } = dispatchProps;

	return {
		...ownProps,
		...stateProps,
		...restDispatchProps,
		onFormSubmit: onFormSubmit( stateProps, dispatch, ownProps ),
		onItemSelect: onItemSelect( stateProps, dispatch, ownProps ),
	};
}

export default compose(
	withStore( { postType: editor.VENUE } ),
	connect( mapStateToProps ),
	withDetails( 'venue' ),
	withForm( ( props ) => props.name ),
	connect( mapStateToProps, mapDispatchToProps, mergeProps ),
)( EventVenue );
