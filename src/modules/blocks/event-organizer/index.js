/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 *
 * Internal dependencies
 */
import Organizer from './container';
import { Organizer as OrganizerIcon } from '@moderntribe/events/icons';
import { editorDefaults } from '@moderntribe/common/utils/globals';

export default {
	id: 'event-organizer',
	title: __( 'Event Organizer', 'events-gutenberg' ),
	description: __( 'List a host or coordinator for this event.', 'events-gutenberg' ),
	icon: <OrganizerIcon/>,
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],

	supports: {
		html: false,
	},

	attributes: {
		organizer: {
			type: 'html',
			default: editorDefaults().organizer ? editorDefaults().organizer : undefined,
		},
		organizers: {
			type: 'array',
			source: 'meta',
			meta: '_EventOrganizerID',
		},
	},

	edit: Organizer,

	save() {
		return null;
	},
};
