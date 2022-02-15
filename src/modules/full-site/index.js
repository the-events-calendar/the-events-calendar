/**
 * Internal Dependencies
 */
import ArchiveEvents from '@moderntribe/events/full-site/archive-events';
import './style.pcss';

const { registerBlockType } = wp.blocks;

const blocks = [
	ArchiveEvents,
];

blocks.forEach( block => {
	const blockName = `tribe/${ block.id }`;
	// registerBlockType( blockName, block );
} );


export default blocks;
