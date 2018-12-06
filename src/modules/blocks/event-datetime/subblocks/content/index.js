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
		timeZone: {
			type: 'string',
			source: 'meta',
			meta: '_EventTimezone',
		},
		separatorDate: {
			type: 'string',
			source: 'meta',
			meta: '_EventDateTimeSeparator',
		},
		separatorTime: {
			type: 'string',
			source: 'meta',
			meta: '_EventTimeRangeSeparator',
		},
		showTimeZone: {
			type: 'boolean',
			default: get( timeZone, 'showtimeZone', false ),
		},
		timeZoneLabel: {
			type: 'string',
			default: get( timeZone, 'label', date.FORMATS.TIMEZONE.string ),
		},
		// Only Available for classic users
		cost: {
			type: 'string',
			source: 'meta',
			meta: '_EventCost',
		},
		currencySymbol: {
			type: 'string',
			source: 'meta',
			meta: '_EventCurrencySymbol',
		},
		currencyPosition: {
			type: 'string',
			source: 'meta',
			meta: '_EventCurrencyPosition',
		},
	},

	edit: EventDateTimeContent,

	save( props ) {
		return null;
	},
};
