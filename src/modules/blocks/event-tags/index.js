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
import EventTags from './block';
import { Tags } from '@moderntribe/events/icons';

/**
 * Module Code
 */
export default {
	id: 'event-tags',
	title: __( 'Tags', 'the-events-calendar' ),
	description: __( 'Add keywords by displaying linked tags.', 'the-events-calendar' ),
	icon: <Tags/>,
	category: 'common',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],

	supports: {
		html: false,
	},

	attributes: {

	},
	edit: EventTags,
	save( props ) {
		return null;
	},
};

