/**
 * External dependencies
 */
import React from 'react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import blockAttributes from './data/attributes';
import EventVenue from './container';
import { Venue } from '@moderntribe/events/icons';

export const blockDefinition = {
	id: 'event-venue',
	title: __( 'Event Venue', 'the-events-calendar' ),
	description: __( 'Where is this event happening? Select or create a location.', 'the-events-calendar' ),
	icon: <Venue />,
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],
	supports: {
		html: false,
	},
	attributes: blockAttributes,
	edit: EventVenue,

	save() {
		return null;
	},
};

/**
 * Register Block
 */
export default registerBlockType( `tribe/${ blockDefinition.id }`, blockDefinition );
