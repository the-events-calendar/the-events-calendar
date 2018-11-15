/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import EventDateTimeDashboard from './container';

export default {
	id: 'event-datetime-dashboard',
	title: __( 'Event Date Time Dashboard', 'events-gutenberg' ),
	description: __(
		'Define the date, time, and duration for your event.',
		'events-gutenberg'
	),
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],
	parent: [ 'tribe/event-datetime' ],

	supports: {
		html: false,
	},

	attributes: {
		start: {
			type: 'string',
			source: 'meta',
			meta: '_EventStartDate',
		},
		end: {
			type: 'string',
			source: 'meta',
			meta: '_EventEndDate',
		},
		allDay: {
			type: 'boolean',
			source: 'meta',
			meta: '_EventAllDay',
		},
	},

	edit: EventDateTimeDashboard,

	save( props ) {
		return null;
	},
};
