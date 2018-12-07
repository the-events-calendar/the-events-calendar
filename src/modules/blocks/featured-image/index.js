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
import FeaturedImage from './block';
import { FeaturedImage as FeaturedImageIcon } from '@moderntribe/events/icons';

/**
 * Module Code
 */
export default {
	id: 'featured-image',
	title: __( 'Featured Image', 'the-events-calendar' ),
	description: __( 'Display the featured image in the post content.', 'the-events-calendar' ),
	icon: <FeaturedImageIcon/>,
	category: 'common',
	keywords: [ 'event', 'events-gutenberg', 'tribe' ],

	supports: {
		html: false,
	},

	attributes: {

	},

	edit: FeaturedImage,
	save( props ) {
		return null;
	},
};

