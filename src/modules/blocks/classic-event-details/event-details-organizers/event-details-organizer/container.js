/**
 * External dependencies
 */
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import { withStore } from '@moderntribe/common/hoc';
import { withDetails } from '@moderntribe/events/hoc';
import EventDetailsOrganizer from './template';

/**
 * Module Code
 */

export default compose(
	withStore(),
	withDetails( 'organizerId' )
)( EventDetailsOrganizer );
