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
import ArchiveEvents from './container';
import { Sharing } from '@moderntribe/events/icons';

/**
 * Module Code
 */
export default {
	id: 'archive-events',
	name: 'tribe/archive-events',
	title: __( 'Archive Events', 'the-events-calendar' ),
	description: __(
		'Encourage visitors to add your event to their calendars with handy sharing buttons.',
		'the-events-calendar',
	),
	icon: <Sharing />,
	category: 'text',
	keywords: [ 'event', 'events-archive', 'tec' ],

	example: {},

	attributes: {
	},

	edit: ArchiveEvents,
	save() {
		return null;
	},
};
