/**
 * WordPress dependencies
 */
import React from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import EventDateTime from './container';
import { DateTime } from '@moderntribe/events/icons';
import { globals } from '@moderntribe/common/utils';

/**
 * Module Code
 */

const ID = 'event-datetime';

export default {
	...globals.blocks()[ ID ],
	title: __( 'Event Date Time', 'the-events-calendar' ),
	description: __(
		'Define the date, time, and duration for your event.',
		'the-events-calendar',
	),
	icon: <DateTime />,
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],

	supports: {
		html: false,
	},

	edit: EventDateTime,

	save() {
		return null;
	},
};
