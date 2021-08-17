/**
 * External dependencies
 */
import { compose } from 'redux';
import { connect } from 'react-redux';
import { uniq } from 'lodash';

/**
 * Internal dependencies
 */
import { globals } from "@moderntribe/common/utils";
import { withStore, withForm } from '@moderntribe/common/hoc';
import { withDetails } from '@moderntribe/events/hoc';
import { actions, selectors } from '@moderntribe/events/data/blocks/organizers';
import { actions as detailsActions } from '@moderntribe/events/data/details';
import { actions as formActions } from '@moderntribe/common/data/forms';
import { editor } from '@moderntribe/common/data';
import { toOrganizer } from '@moderntribe/events/elements/organizer-form/utils';
import classicEventDetailsBlock from '@moderntribe/events/blocks/classic-event-details';
import EventOrganizer from './template';

/**
 * Module Code
 */

const addOrganizer = ( { state, dispatch, ownProps, organizerID, details } ) => {
	const organizers = selectors.getOrganizersInClassic( state );

	ownProps.setAttributes( { organizer: organizerID } );
	ownProps.setAttributes( { organizers: uniq( [ ...organizers, organizerID ] ) } );

	dispatch( detailsActions.setDetails( organizerID, details ) );
	dispatch( actions.addOrganizerInClassic( organizerID ) );
	dispatch( actions.addOrganizerInBlock( ownProps.clientId, organizerID ) );
};

const onFormCompleted = ( state, dispatch, ownProps ) => ( body = {} ) => {
	addOrganizer( { state, dispatch, ownProps, organizerID: body.id, details: body } );
};

const mapStateToProps = ( state, ownProps ) => ( {
	/**
	 * @todo: the organizer prop is needed for withDetails, remove this if we fix it
	 */
	organizer: ownProps.attributes.organizer,
	organizers: selectors.getOrganizersInBlock( state ),
	state,
} );

const mapDispatchToProps = ( dispatch, ownProps ) => ( {
	onCreateNew: ( title ) => {
		ownProps.createDraft( {
			title: {
				rendered: title,
			},
		} );
	},
	onEdit: () => {
		ownProps.editEntry( ownProps.details );
	},
	dispatch,
} );

const mergeProps = ( stateProps, dispatchProps, ownProps ) => {
	const { state, ...restStateProps } = stateProps;
	const { dispatch, ...restDispatchProps } = dispatchProps;

	return {
		...ownProps,
		...restStateProps,
		...restDispatchProps,
		onFormSubmit: ( fields ) => {
			ownProps.sendForm( toOrganizer( fields ), onFormCompleted( state, dispatch, ownProps ) );
		},
		onItemSelect: ( organizerID, details ) => {
			addOrganizer( { state, dispatch, ownProps, organizerID, details } );
		},
		onRemove: () => {
			const { clientId, organizer, details, volatile } = ownProps;

			ownProps.setAttributes( { organizer: 0 } );
			dispatch( actions.removeOrganizerInBlock( clientId, organizer ) );

			const blocks = globals.wpDataSelectCoreEditor().getBlocks();
			const classicBlock = blocks.filter( block => block.name === `tribe/${ classicEventDetailsBlock.id }` );

			if ( ! classicBlock.length || volatile ) {
				ownProps.maybeRemoveEntry( details );

				const organizers = selectors.getOrganizersInClassic( state );
				const newOrganizers = organizers.filter( id => id !== organizer );

				ownProps.setAttributes( { organizers: newOrganizers } );
				dispatch( actions.removeOrganizerInClassic( organizer ) );
				dispatch( formActions.removeVolatile( organizer ) );
			}
		},
	};
};

export default compose(
	withStore( { isolated: true, postType: editor.ORGANIZER } ),
	withForm( ( props ) => props.clientId ),
	connect( mapStateToProps ),
	withDetails( 'organizer' ),
	connect( mapStateToProps, mapDispatchToProps, mergeProps ),
)( EventOrganizer );
