/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import EventsList from './template';
import { EventsListIcon } from '@moderntribe/events/icons';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const { InnerBlocks } = wp.editor;

/**
 * Module Code
 */
export default {
	id: 'events-list',
	title: __( 'Events List', 'tribe-events-calendar-pro' ),
	description: __( 'Display events list', 'tribe-events-calendar-pro' ),
	icon: <EventsListIcon />,
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],

	edit: EventsList,
	save: () => {
		return (
			<InnerBlocks.Content />
		);
	},
};

