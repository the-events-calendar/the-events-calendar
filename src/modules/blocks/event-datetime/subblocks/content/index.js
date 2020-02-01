/*
 * External Dependencies
 */
import React from 'react';
import { get } from 'lodash';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { globals, date } from '@moderntribe/common/utils';
import { DateTime } from '@moderntribe/events/icons';

/**
 * Internal dependencies
 */
import EventDateTimeContent from './container';

const timeZone = get( globals.tec(), 'timeZone', {} );

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
