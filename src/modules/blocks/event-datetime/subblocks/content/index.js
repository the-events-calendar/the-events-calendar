/*
 * External Dependencies
 */
import React from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { DateTime } from '@moderntribe/events/icons';

/**
 * Internal dependencies
 */
import EventDateTimeContent from './container';

export default {
	id: 'event-datetime-content',
	title: __( 'Event Date Time Content', 'the-events-calendar' ),
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
		// Only Available for classic users
		cost: {
			type: 'string',
			source: 'meta',
			meta: '_EventCost',
		},
	},

	edit: EventDateTimeContent,

	save( props ) {
		return null;
	},
};
