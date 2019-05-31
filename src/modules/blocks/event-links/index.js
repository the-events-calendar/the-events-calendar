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
import EventLinks from './container';
import { Sharing } from '@moderntribe/events/icons';

/**
 * Module Code
 */
export default {
	id: 'event-links',
	title: __( 'Event Sharing', 'the-events-calendar' ),
	description: __(
		'Encourage visitors to add your event to their calendars with handy sharing buttons.',
		'the-events-calendar'
	),
	icon: <Sharing/>,
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],

	supports: {
		html: false,
	},

	attributes: {
		googleCalendarLabel: {
			type: 'html',
			default: __( 'Google Calendar', 'the-events-calendar' ),
		},
		iCalLabel: {
			type: 'html',
			default: __( 'iCal Export', 'the-events-calendar' ),
		},
		hasiCal: {
			type: 'html',
			default: true,
		},
		hasGoogleCalendar: {
			type: 'html',
			default: true,
		},
	},

	edit: EventLinks,
	save( props ) {
		return null;
	},
};

