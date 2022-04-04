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
import ClassicEventDetails from './container';
import { Classic } from '@moderntribe/events/icons';

/**
 * Module Code
 */
export default {
	id: 'classic-event-details',
	title: __( 'Event Details Classic', 'the-events-calendar' ),
	description: __(
		'Display your event info together in one place — just like in the Classic Editor.',
		'the-events-calendar',
	),
	icon: <Classic />,
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],

	supports: {
		html: false,
	},

	attributes: {
		organizerTitle: {
			type: 'html',
			default: '',
		},
		detailsTitle: {
			type: 'html',
			default: '',
		},
		organizers: {
			type: 'array',
			source: 'meta',
			meta: '_EventOrganizerID',
		},
		allDay: {
			type: 'boolean',
			source: 'meta',
			meta: '_EventAllDay',
		},
		url: {
			type: 'string',
			source: 'meta',
			meta: '_EventURL',
		},
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
		currencyCode: {
			type: 'string',
			source: 'meta',
			meta: '_EventCurrencyCode',
		},
		currencyPosition: {
			type: 'string',
			source: 'meta',
			meta: '_EventCurrencyPosition',
		},
	},

	edit: ClassicEventDetails,

	save() {
		return null;
	},
};
