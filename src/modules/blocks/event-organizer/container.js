/**
 * External dependencies
 */
import { compose } from 'redux';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { withStore, withForm } from '@moderntribe/common/hoc';
import { withDetails } from '@moderntribe/events/hoc';
import { actions, selectors } from '@moderntribe/events/data/blocks/organizers';
import { actions as detailsActions } from '@moderntribe/events/data/details';
import { actions as formActions } from '@moderntribe/common/data/forms';
import { editor } from '@moderntribe/common/data';
import EventOrganizer from './template';
import { toOrganizer } from '@moderntribe/events/elements/organizer-form/utils';

/**
 * Module Code
 */

const onFormCompleted = ( dispatch ) => ( body = {} ) => {
	dispatch( detailsActions.setDetails( body.id, body ) );
	dispatch( actions.addOrganizerInClassic( body.id ) );
	dispatch( actions.addOrganizerInBlock( body.id ) );
};

const mapStateToProps = ( state ) => ( {
	organizers: selectors.getOrganizersInBlock( state ),
} );

const mapDispatchToProps = ( dispatch, ownProps ) => ( {
	onFormSubmit: ( fields ) => {
		ownProps.sendForm( toOrganizer( fields ), onFormCompleted( dispatch ) );
	},
	onItemSelect: ( organizerID, details ) => {
		dispatch( detailsActions.setDetails( organizerID, details ) );
		dispatch( actions.addOrganizerInClassic( organizerID ) );
		dispatch( actions.addOrganizerInBlock( organizerID ) );
	},
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
	onRemove: () => {
		const { organizer, details, volatile } = ownProps;
		dispatch( actions.removeOrganizerInBlock( organizer ) );

		if ( volatile ) {
			maybeRemoveEntry( details );
			dispatch( actions.removeOrganizerInClassic( organizer ) );
		}
	},
	onBlockAdded: () => {
		if ( ! ownProps.organizer ) {
			return;
		}

		dispatch( actions.addOrganizerInBlock( organizer ) );
	},
	onBlockRemoved: () => {
		const { organizer, volatile } = ownProps;
		if ( ! organizer ) {
			return;
		}

		dispatch( actions.removeOrganizerInBlock( organizer ) );

		if ( volatile ) {
			dispatch( actions.removeOrganizerInClassic( organizer ) );
			/**
			 * @todo: this one creates a connection with the Form event, however the form has no idea of
			 * @todo: the ID to be removed so this one might be a good saga watcher
			 */
			dispatch( formActions.removeVolatile( organizer ) );
		}
	},
} );

export default compose(
	withStore( { isolated: true, postType: editor.ORGANIZER } ),
	withForm( ( props ) => props.clientId ),
	connect( mapStateToProps ),
	withDetails( 'organizer' ),
	connect( null, mapDispatchToProps ),
)( EventOrganizer );
