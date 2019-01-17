/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */
import React from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import EventDateTimeDashboard from './container';
import { DateTime } from '@moderntribe/events/icons';

export default {
	id: 'event-datetime-dashboard',
	title: __( 'Event Date Time Dashboard', 'the-events-calendar' ),
	description: __(
		'Define the date, time, and duration for your event.',
		'the-events-calendar'
	),
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],
	parent: [ 'tribe/event-datetime' ],
	icon: <DateTime />,
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
		separatorTime: {
			type: 'string',
			source: 'meta',
			meta: '_EventTimeRangeSeparator',
		},
	},

	edit: EventDateTimeDashboard,

	save( props ) {
		return null;
	},
};
