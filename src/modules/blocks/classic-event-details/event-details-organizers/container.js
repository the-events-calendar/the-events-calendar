/**
 * External dependencies
 */
import { compose } from 'redux';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import {
	actions as organizersActions,
	selectors as organizersSelectors,
} from '@moderntribe/events/data/blocks/organizers';
import { actions as detailsActions } from '@moderntribe/events/data/details';
import { withStore } from '@moderntribe/common/hoc';
import EventDetailsOrganizers from './template';

/**
 * Module Code
 */

const addOrganizer = ( dispatch ) => ( id, details ) => {
	dispatch( detailsActions.setDetails( id, details ) );
	dispatch( organizersActions.addOrganizerInClassic( id ) );
};

const removeOrganizer = ( dispatch ) => ( id ) => () => (
	dispatch( organizersActions.removeOrganizerInClassic( id ) )
);

const mapStateToProps = ( state ) => ( {
	organizers: organizersSelectors.getMappedOrganizers( state ),
} );

const mapDispatchToProps = ( dispatch ) => ( {
	addOrganizer: addOrganizer( dispatch ),
	removeOrganizer: removeOrganizer( dispatch ),
} );

export default compose(
	withStore(),
	connect( mapStateToProps, mapDispatchToProps ),
)( EventDetailsOrganizers );
