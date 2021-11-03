/**
 * External Dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal Dependencies
 */
import EventsList from '@moderntribe/events/widgets/events-list';
import { initStore } from '@moderntribe/events/data';

const blocks = [
	EventsList,
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
