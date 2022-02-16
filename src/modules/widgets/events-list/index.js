/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import EventsList from './template';
import { EventsList as EventsListIcon } from '@moderntribe/events/icons';

const { __ } = wp.i18n;
const { InnerBlocks } = wp.blockEditor;

/**
 * Module Code
 */
export default {
	id: 'events-list',
	title: __( 'Events List', 'the-events-calendar' ),
	description: __( 'Shows a list of upcoming events.', 'the-events-calendar' ),
	icon: <EventsListIcon />,
	category: 'tribe-events',
	keywords: [ 'event', 'events list', 'list', 'events-gutenberg', 'tribe' ],
	example: {},

	edit: EventsList,
	save: () => <InnerBlocks.Content />,
};

