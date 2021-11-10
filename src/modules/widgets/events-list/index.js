/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import EventsList from './template';
import { EventsListIcon } from '@moderntribe/events/icons';

const { __ } = wp.i18n;
const { InnerBlocks } = wp.editor;

/**
 * Module Code
 */
export default {
	id: 'events-list',
	title: __( 'Events List', 'the-events-calendar' ),
	description: __( 'Display events list', 'the-events-calendar' ),
	icon: <EventsListIcon />,
	category: 'tribe-events',
	keywords: [ 'event', 'events list', 'list', 'events-gutenberg', 'tribe' ],
	example: {},

	edit: EventsList,
	save: () => <InnerBlocks.Content />,
};

