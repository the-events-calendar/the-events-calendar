/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import EventWebsite from './container';
import { Website } from '@moderntribe/events/icons';

/**
 * Module Code
 */
export default {
	id: 'event-website',
	title: __( 'Event Website', 'the-events-calendar' ),
	description: __(
		'Is there another website for this event? Link to it with a button!',
		'the-events-calendar'
	),
	icon: <Website/>,
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],

	supports: {
		html: false,
	},

	attributes: {
		urlLabel: {
			type: 'html',
			default: '',
		},
		url: {
			type: 'string',
			source: 'meta',
			meta: '_EventURL',
		},
	},

	edit: EventWebsite,

	save( props ) {
		return null;
	},
};
