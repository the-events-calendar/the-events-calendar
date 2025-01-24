/**
 * External dependencies
 */
import React, { useEffect } from 'react';
import { compose } from 'redux';
import { connect } from 'react-redux';
import { uniq } from 'lodash';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { globals } from '@moderntribe/common/utils';
import { withStore, withForm } from '@moderntribe/common/hoc';
import { withDetails } from '@moderntribe/events/hoc';
import { actions, selectors } from '@moderntribe/events/data/blocks/organizers';
import { actions as detailsActions } from '@moderntribe/events/data/details';
import { actions as formActions } from '@moderntribe/common/data/forms';
import { editor } from '@moderntribe/common/data';
import { toOrganizer } from '@moderntribe/events/elements/organizer-form/utils';
import classicEventDetailsBlock from '@moderntribe/events/blocks/classic-event-details';
import EventOrganizer from './template';
import { editorDefaults } from '@moderntribe/common/utils/globals';

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
			// Request with cleaned up form fields, callback will dispatch to create new organizer details component.
			ownProps.sendForm( toOrganizer( fields ), onFormCompleted( state, dispatch, ownProps ) );
		},
		onItemSelect: ( organizerID, details ) => {
			addOrganizer( { state, dispatch, ownProps, organizerID, details } );
		},
		onRemove: () => {
			const { clientId, organizer, volatile } = ownProps;

			ownProps.setAttributes( { organizer: 0 } );
			dispatch( actions.removeOrganizerInBlock( clientId, organizer ) );

			/**
			 * Moves the organizer to the trash if appropriate (if it is a draft and was removed).
			 *
			 * @since 6.2.0
			 * @param {number} organizer
			 */
			globals.wpHooks.doAction( 'tec.events.blocks.organizer.maybeRemoveOrganizer', organizer );

			const blocks = globals.wpDataSelectCoreEditor().getBlocks();
			const classicBlock = blocks
				.filter( block => block.name === `tribe/${ classicEventDetailsBlock.id }` );

			if ( ! classicBlock.length || volatile ) {
				const organizers = selectors.getOrganizersInClassic( state );
				const newOrganizers = organizers.filter( id => id !== organizer );

				ownProps.setAttributes( { organizers: newOrganizers } );
				dispatch( actions.removeOrganizerInClassic( organizer ) );
				dispatch( formActions.removeVolatile( organizer ) );
			}
		},
	};
};

/**
 * Our Event Organizer blocks container. This is responsible for managing the state passed down to the template.
 *
 * @param props The props with the organizer and `setAttributes` function, that will be passed down to the
 * 				EventOrganizer component.
 * @returns {JSX.IntrinsicElements} Returns the EventOrganizer component.
 * @constructor
 */
const StatefulEventOrganizer = ( props ) => {
	// This hook should only run once, it checks for default values.
	useEffect( () => {
		// Manage our initial state for defaults.
		const defaults = editorDefaults();
		const { attributes: { organizer } } = props;

		if ( organizer === null && defaults && defaults.organizer ) {
			props.setAttributes( { organizer: defaults.organizer } );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	return (
		<EventOrganizer { ...props } />
	);
};

StatefulEventOrganizer.propTypes = {
	attributes: PropTypes.object,
	setAttributes: PropTypes.func,
};

export default compose(
	withStore( { isolated: true, postType: editor.ORGANIZER } ),
	withForm( ( props ) => props.clientId ),
	connect( mapStateToProps ),
	withDetails( 'organizer' ),
	connect( mapStateToProps, mapDispatchToProps, mergeProps ),
)( StatefulEventOrganizer );
