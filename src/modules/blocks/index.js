/**
 * External Dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal Dependencies
 */
import classicEventDetails from '@moderntribe/events/blocks/classic-event-details';
import EventDateTimeControls from '@moderntribe/events/blocks/event-datetime/controls';
import eventDateTime from '@moderntribe/events/blocks/event-datetime';
import eventVenue from '@moderntribe/events/blocks/event-venue';
import eventOrganizer from '@moderntribe/events/blocks/event-organizer';
import eventLinks from '@moderntribe/events/blocks/event-links';
import eventPrice from '@moderntribe/events/blocks/event-price';
import eventCategory from '@moderntribe/events/blocks/event-category';
import eventTags from '@moderntribe/events/blocks/event-tags';
import eventWebsite from '@moderntribe/events/blocks/event-website';
import FeaturedImage from '@moderntribe/events/blocks/featured-image';
import { initStore } from '@moderntribe/events/data';
import './style.pcss';

// Used by events-pro blocks
export const controls = {
	EventDateTimeControls,
};

const blocks = [
	classicEventDetails,
	eventDateTime,
	eventVenue,
	eventOrganizer,
	eventLinks,
	eventPrice,
	eventCategory,
	eventTags,
	eventWebsite,
	FeaturedImage,
];

blocks.forEach( block => {
	const blockName = `tribe/${ block.id }`;
	registerBlockType( blockName, block );
} );

// Initialize AFTER blocks are registered
// to avoid plugin shown as available in reducer
// but not having block available for use
initStore();

export default blocks;
