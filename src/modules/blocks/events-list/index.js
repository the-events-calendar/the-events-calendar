/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import EventsList from './template';

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
	icon: <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M20 20h-4v-4h4v4zm-6-10h-4v4h4v-4zm6 0h-4v4h4v-4zm-12 6h-4v4h4v-4zm6 0h-4v4h4v-4zm-6-6h-4v4h4v-4zm16-8v22h-24v-22h3v1c0 1.103.897 2 2 2s2-.897 2-2v-1h10v1c0 1.103.897 2 2 2s2-.897 2-2v-1h3zm-2 6h-20v14h20v-14zm-2-7c0-.552-.447-1-1-1s-1 .448-1 1v2c0 .552.447 1 1 1s1-.448 1-1v-2zm-14 2c0 .552-.447 1-1 1s-1-.448-1-1v-2c0-.552.447-1 1-1s1 .448 1 1v2z" /></svg>, // eslint-disable-line max-len
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],

	edit: EventsList,
	save: () => {
		return (
			<InnerBlocks.Content />
		);
	},
};

