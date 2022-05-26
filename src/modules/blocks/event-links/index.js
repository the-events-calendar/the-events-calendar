/**
 * External dependencies
 */
import React from 'react';

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
		'the-events-calendar',
	),
	icon: <Sharing />,
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
			default: __( 'iCalendar', 'the-events-calendar' ),
		},
		outlook365Label: {
			type: 'html',
			default: __( 'Outlook 365', 'the-events-calendar' ),
		},
		outlookLiveLabel: {
			type: 'html',
			default: __( 'Outlook Live', 'the-events-calendar' ),
		},
		hasiCal: {
			type: 'html',
			default: true,
		},
		hasGoogleCalendar: {
			type: 'html',
			default: true,
		},
		hasOutlook365: {
			type: 'html',
			default: true,
		},
		hasOutlookLive: {
			type: 'html',
			default: true,
		},
	},

	edit: EventLinks,
	save() {
		return null;
	},
};
