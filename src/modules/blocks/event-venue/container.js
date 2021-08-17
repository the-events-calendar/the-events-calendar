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

const setVenue = ( dispatch, ownProps ) => ( id ) => {
	ownProps.setAttributes( { venue: id } );
	dispatch( actions.setVenue( id ) );
};

const onFormComplete = ( dispatch, ownProps ) => ( body ) => {
	const { setDetails } = ownProps;
	const { id } = body;
	setDetails( id, body );
	setVenue( dispatch, ownProps )( id );
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

// @todo [BTRIA-619]: need to remove the use of "maybe" functions as they hold logic they
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
	onFormSubmit: onFormSubmit( dispatch, ownProps ),
	onItemSelect: onItemSelect( dispatch, ownProps ),
} );

export default compose(
	withStore( { postType: editor.VENUE } ),
	connect( mapStateToProps ),
	withDetails( 'venue' ),
	withForm( ( props ) => props.name ),
	connect( mapStateToProps, mapDispatchToProps ),
)( EventVenue );
