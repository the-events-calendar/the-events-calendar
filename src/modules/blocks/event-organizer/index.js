/**
 * External dependencies
 */
import React from 'react';

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

export default {
	id: 'event-organizer',
	title: __( 'Event Organizer', 'the-events-calendar' ),
	description: __( 'List a host or coordinator for this event.', 'the-events-calendar' ),
	icon: <OrganizerIcon />,
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],

	supports: {
		html: false,
	},

	attributes: {
		organizer: {
			type: 'html',
			default: null,
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
