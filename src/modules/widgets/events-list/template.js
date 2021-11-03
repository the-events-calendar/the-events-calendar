/**
 * External dependencies
 */
import React from 'react';

/**
 * WordPress dependencies
 */
const { InnerBlocks } = wp.editor;

const EVENTS_LIST_TEMPLATE = [
	[
		'core/legacy-widget',
		{
			idBase: 'tribe-widget-events-list',
			instance: {},
		},
	],
];

const EventsList = () => (
	<InnerBlocks
		template={ EVENTS_LIST_TEMPLATE }
		templateLock="all"
	/>
);

export default EventsList;
