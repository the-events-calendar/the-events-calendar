/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import EventVenue from './container';
import { Venue } from '@moderntribe/events/icons';

/**
 * Module Code
 */
export default {
	id: 'event-venue',
	title: __( 'Event Venue', 'the-events-calendar' ),
	description: __(
		'Where is this event happening? Select or create a location.',
		'the-events-calendar'
	),
	icon: <Venue/>,
	category: 'tribe-events',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],

	supports: {
		html: false,
	},

	attributes: {
		venueTitle: {
			type: 'html',
		},
		venue: {
			type: 'integer',
			source: 'meta',
			meta: '_EventVenueID',
		},
		showMapLink: {
			type: 'boolean',
			source: 'meta',
			meta: '_EventShowMapLink',
		},
		showMap: {
			type: 'boolean',
			source: 'meta',
			meta: '_EventShowMap',
		},
	},

	edit: EventVenue,

	save( props ) {
		return null;
	},
};

