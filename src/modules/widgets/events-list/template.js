/**
 * External dependencies
 */
import React from 'react';

const { InnerBlocks } = wp.blockEditor;

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
